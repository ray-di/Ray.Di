<?php

declare(strict_types=1);

namespace Ray\Di;

use BadMethodCallException;
use Ray\Aop\Compiler;
use Ray\Aop\CompilerInterface;
use Ray\Aop\Pointcut;
use Ray\Di\Exception\CircularDependency;
use Ray\Di\Exception\ConcurrentSingletonConstruction;
use Ray\Di\Exception\NoHint;
use Ray\Di\Exception\RenameTargetAlreadyBound;
use Ray\Di\Exception\Unbound;
use Ray\Di\Exception\Untargeted;
use Ray\Di\MultiBinding\MultiBindings;
use ReflectionClass;

use function array_filter;
use function array_intersect_key;
use function array_keys;
use function array_merge;
use function array_values;
use function class_exists;
use function explode;
use function implode;
use function ksort;
use function sprintf;

/**
 * @psalm-import-type DependencyContainer from Types
 * @psalm-import-type PointcutList from Types
 * @psalm-import-type DependencyIndex from Types
 * @psalm-import-type MethodArguments from Types
 * @psalm-import-type InjectableValue from Types
 */
final class Container implements InjectorInterface
{
    /**
     * Bookkeeping binding installed by MultiBinder, not a user binding,
     * so it is excluded from the BindingLog
     */
    private const MULTI_BINDINGS_INDEX = MultiBindings::class . '-' . Name::ANY;

    private const INJECTION_POINT_INDEX = InjectionPointInterface::class . '-' . Name::ANY;

    /** @var MultiBindings */
    public $multiBindings;

    /** @var DependencyContainer */
    private array $container = [];

    /** @var array<int, Pointcut> */
    private array $pointcuts = [];

    /**
     * Dependency indexes currently being resolved, keyed by coroutine id
     * (0 when outside a coroutine), used to detect circular dependencies
     *
     * Per-coroutine so that a coroutine suspending mid-resolution does not
     * make another coroutine resolve the same index look like a cycle.
     * Not serialized: resolution state is always empty between getInstance() calls.
     *
     * @var array<int, array<string, true>>
     */
    private array $resolving = [];

    /**
     * Singleton indexes currently under construction, set only inside
     * coroutines; a second coroutine finding its index here throws
     * ConcurrentSingletonConstruction instead of building a duplicate
     *
     * Not serialized (see $resolving).
     *
     * @var array<string, true>
     */
    private array $constructing = [];

    /**
     * Per-coroutine injection point overlay, keyed by coroutine id
     *
     * Outside coroutines the injection point lives in the container entry as
     * before; inside coroutines it is kept per coroutine so that a coroutine
     * suspending mid-resolution cannot observe another coroutine's point.
     * Not serialized (see $resolving).
     *
     * @var array<int, InjectionPointInterface>
     */
    private array $injectionPoints = [];

    /**
     * Composition-time binding history
     *
     * Not serialized (excluded from __sleep()); re-created empty on __wakeup()
     * so a revived container never carries build-time history into runtime.
     */
    public BindingLog $log;

    public function __construct()
    {
        $this->multiBindings = new MultiBindings();
        $this->log = new BindingLog();
    }

    /** @return list<string> */
    public function __sleep()
    {
        return ['container', 'pointcuts', 'multiBindings'];
    }

    public function __wakeup(): void
    {
        $this->log = new BindingLog();
    }

    /**
     * Add binding to a container
     */
    public function add(Bind $bind): void
    {
        $index = (string) $bind;
        $previous = $this->container[$index] ?? null;
        $dependency = $bind->getBound();
        $dependency->register($this->container, $bind);
        if ($index === self::MULTI_BINDINGS_INDEX) {
            return;
        }

        /** @psalm-suppress InvalidArrayAccess -- register()'s @param-out leaves the DependencyContainer alias unexpanded */
        $this->log->register(
            $index,
            (string) $this->container[$index],
            $previous === null ? null : (string) $previous,
            $bind->source !== '' ? $bind->source : 'unknown'
        );
    }

    /**
     * Set the current injection point, returning the previous one so it can be restored
     *
     * @internal
     */
    public function setInjectionPoint(InjectionPointInterface $ip): ?InjectionPointInterface
    {
        $cid = CoroutineContext::id();
        if ($cid > 0) {
            $previous = $this->injectionPoints[$cid] ?? null;
            $this->injectionPoints[$cid] = $ip;

            return $previous;
        }

        $existing = $this->container[self::INJECTION_POINT_INDEX] ?? null;
        $previous = $existing instanceof Instance && $existing->value instanceof InjectionPointInterface
            ? $existing->value
            : null;
        $this->container[self::INJECTION_POINT_INDEX] = new Instance($ip);

        return $previous;
    }

    /**
     * Restore a previously saved injection point
     *
     * @internal
     */
    public function restoreInjectionPoint(?InjectionPointInterface $ip): void
    {
        $cid = CoroutineContext::id();
        if ($cid > 0) {
            if ($ip === null) {
                unset($this->injectionPoints[$cid]);

                return;
            }

            $this->injectionPoints[$cid] = $ip;

            return;
        }

        if ($ip === null) {
            unset($this->container[self::INJECTION_POINT_INDEX]);

            return;
        }

        $this->container[self::INJECTION_POINT_INDEX] = new Instance($ip);
    }

    /**
     * Add Pointcut to container
     */
    public function addPointcut(Pointcut $pointcut): void
    {
        $this->pointcuts[] = $pointcut;
    }

    /**
     * {@inheritDoc}
     *
     * @param ''|class-string<T> $interface
     * @param string             $name
     *
     * @return ($interface is '' ? mixed : T)
     *
     * @template T of object
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        /** @psalm-suppress MixedReturnStatement */
        return $this->getDependency($interface . '-' . $name);
    }

    /**
     * Return dependency injected instance
     *
     * @param MethodArguments $params
     *
     * @return mixed
     *
     * @throws Unbound
     */
    public function getInstanceWithArgs(string $interface, array $params)
    {
        $index = $interface . '-';
        if (! isset($this->container[$index])) {
            throw $this->unbound($index);
        }

        $dependency = $this->container[$index];
        if (! $dependency instanceof Dependency) {
            throw new BadMethodCallException($interface);
        }

        return $this->injectSingletonOnce($index, $dependency, fn (): mixed => $dependency->injectWithArgs($this, $params));
    }

    /**
     * Return dependency injected instance
     *
     * @param DependencyIndex $index
     *
     * @return mixed
     *
     * @throws Unbound
     */
    public function getDependency(string $index)
    {
        if ($index === self::INJECTION_POINT_INDEX) {
            $injectionPoint = $this->injectionPoints[CoroutineContext::id()] ?? null;
            if ($injectionPoint !== null) {
                return $injectionPoint;
            }
        }

        if (! isset($this->container[$index])) {
            throw $this->unbound($index);
        }

        $cid = CoroutineContext::id();
        if (isset($this->resolving[$cid][$index])) {
            $dependency = $this->container[$index];
            // An already-instantiated singleton satisfies re-entrant requests
            // (e.g. from a @PostConstruct method) with its cached instance,
            // without recursing — not a cycle.
            if ($dependency instanceof Dependency && $dependency->isInstantiated()) {
                return $dependency->inject($this);
            }

            throw new CircularDependency(sprintf("'%s'", implode(' -> ', [...array_keys($this->resolving[$cid]), $index])));
        }

        $this->resolving[$cid][$index] = true;
        try {
            $dependency = $this->container[$index];

            return $this->injectSingletonOnce($index, $dependency, fn (): mixed => $dependency->inject($this));
        } finally {
            unset($this->resolving[$cid][$index]);
            if ($this->resolving[$cid] === []) {
                unset($this->resolving[$cid]);
            }
        }
    }

    /**
     * Inject, refusing to build a singleton concurrently in another coroutine
     *
     * Outside coroutines and for already-built or non-singleton dependencies
     * this is a plain inject. Inside a coroutine, the first coroutine to
     * build a singleton marks its index; a second coroutine finding the mark
     * throws ConcurrentSingletonConstruction rather than building a duplicate
     * (e.g. a second connection pool). Warm up singletons before serving
     * requests so construction never races.
     *
     * @param callable(): mixed $inject
     *
     * @return mixed
     */
    private function injectSingletonOnce(string $index, DependencyInterface $dependency, callable $inject)
    {
        if (CoroutineContext::id() === 0 || ! self::isUnbuiltSingleton($dependency)) {
            return $inject();
        }

        if (isset($this->constructing[$index])) {
            throw new ConcurrentSingletonConstruction(sprintf("'%s'", $index));
        }

        $this->constructing[$index] = true;
        try {
            return $inject();
        } finally {
            unset($this->constructing[$index]);
        }
    }

    private static function isUnbuiltSingleton(DependencyInterface $dependency): bool
    {
        if ($dependency instanceof Dependency) {
            return $dependency->isSingleton() && ! $dependency->isInstantiated();
        }

        if ($dependency instanceof DependencyProvider) {
            return $dependency->isSingleton() && ! $dependency->isInstantiated();
        }

        return false;
    }

    /**
     * Rename existing dependency interface + name
     *
     * @throws Unbound                  When no binding exists at the source index.
     * @throws RenameTargetAlreadyBound When a binding already exists at the target index.
     */
    public function move(string $sourceInterface, string $sourceName, string $targetInterface, string $targetName): void
    {
        $sourceIndex = $sourceInterface . '-' . $sourceName;
        if (! isset($this->container[$sourceIndex])) {
            throw $this->unbound($sourceIndex);
        }

        $targetIndex = $targetInterface . '-' . $targetName;
        if ($targetIndex !== $sourceIndex) {
            if (isset($this->container[$targetIndex])) {
                throw new RenameTargetAlreadyBound(sprintf("'%s'", $targetIndex));
            }

            $this->container[$targetIndex] = $this->container[$sourceIndex];
            unset($this->container[$sourceIndex]);
            $this->log->move($sourceIndex, $targetIndex);
        }
    }

    /**
     * Return Unbound exception
     *
     * @param DependencyIndex $index {interface}-{bind name}
     */
    public function unbound(string $index): Untargeted|Unbound
    {
        /** @psalm-suppress PossiblyUndefinedArrayOffset -- $index is always "{interface}-{name}" */
        [$class, $name] = explode('-', $index, 2);
        if (class_exists($class) && (new ReflectionClass($class))->isInstantiable()) {
            return new Untargeted($class);
        }

        if ($class === '' && $name === '') {
            return new NoHint();
        }

        return new Unbound(sprintf("'%s-%s'", $class, $name));
    }

    /**
     * Return container
     *
     * @return array<non-empty-string, DependencyInterface>
     * @psalm-return DependencyContainer
     */
    public function getContainer(): array
    {
        return $this->container;
    }

    /**
     * Return pointcuts
     *
     * @return array<int, Pointcut>
     * @psalm-return PointcutList
     */
    public function getPointcuts(): array
    {
        return $this->pointcuts;
    }

    /**
     * Merge container
     *
     * The collision history is recorded first: `+=` lets existing entries win
     * and silently discards the incoming side, so each colliding index is
     * logged as a keep event before the merge makes the discard invisible.
     */
    public function merge(self $container): void
    {
        $otherContainer = $container->getContainer();
        // iterate the incoming (usually smaller) side: O(incoming), not O(accumulated)
        $collidingIndexes = array_keys(array_intersect_key($otherContainer, $this->container));
        // MultiBindings collides on every merge by design; excluded from the log (see MULTI_BINDINGS_INDEX)
        $collidingIndexes = array_values(array_filter(
            $collidingIndexes,
            static fn (string $index): bool => $index !== self::MULTI_BINDINGS_INDEX
        ));
        $keptDependencies = [];
        $discardedDependencies = [];
        foreach ($collidingIndexes as $index) {
            $keptDependencies[$index] = (string) $this->container[$index];
            $discardedDependencies[$index] = (string) $otherContainer[$index];
        }

        $this->log->merge($container->log, $collidingIndexes, $keptDependencies, $discardedDependencies);

        $this->multiBindings->merge($container->multiBindings);
        $this->container += $otherContainer;
        $this->pointcuts = array_merge($this->pointcuts, $container->getPointcuts());
        $this->bindMergedMultiBindings();
    }

    /** Re-point the MultiBindings binding at the merged store */
    private function bindMergedMultiBindings(): void
    {
        $index = self::MULTI_BINDINGS_INDEX;
        if (! isset($this->container[$index])) {
            return;
        }

        $this->container[$index] = new Instance($this->multiBindings);
    }

    /**
     * Weave aspects to all dependency in container
     */
    public function weaveAspects(CompilerInterface $compiler): void
    {
        if ($this->pointcuts === []) {
            return;
        }

        foreach ($this->container as $dependency) {
            if ($dependency instanceof Dependency) {
                $dependency->weaveAspects($compiler, $this->pointcuts);
            }
        }
    }

    /**
     * Weave aspect to single dependency
     */
    public function weaveAspect(Compiler $compiler, Dependency $dependency): self
    {
        $dependency->weaveAspects($compiler, $this->pointcuts);

        return $this;
    }

    /** @param callable(DependencyInterface, string): DependencyInterface $f */
    public function map(callable $f): void
    {
        foreach ($this->container as $key => &$index) {
            $index = $f($index, $key);
        }
    }

    public function sort(): void
    {
        ksort($this->container);
    }
}
