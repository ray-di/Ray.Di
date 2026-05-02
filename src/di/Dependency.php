<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\Bind as AopBind;
use Ray\Aop\CompilerInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\WeavedInterface;
use ReflectionClass;
use ReflectionMethod;

use function assert;
use function method_exists;
use function sprintf;

/**
 * @psalm-import-type MethodArguments from Types
 * @psalm-import-type PointcutList from Types
 */
final class Dependency implements DependencyInterface, AcceptInterface
{
    /** @var NewInstance */
    private $newInstance;

    /** @var ?string */
    private $postConstruct;
    private bool $isSingleton = false;
    private bool $isInstantiated = false;

    /** @var ?mixed */
    private $instance;

    public function __construct(NewInstance $newInstance, ?ReflectionMethod $postConstruct = null)
    {
        $this->newInstance = $newInstance;
        $this->postConstruct = $postConstruct->name ?? null;
    }

    /**
     * @return array<string>
     */
    public function __sleep()
    {
        return ['newInstance', 'postConstruct', 'isSingleton'];
    }

    public function __toString(): string
    {
        return sprintf(
            '(dependency) %s',
            (string) $this->newInstance
        );
    }

    /**
     * {@inheritdoc}
     */
    public function register(array &$container, Bind $bind): void
    {
        $container[(string) $bind] = $bind->getBound();
    }

    /**
     * {@inheritdoc}
     */
    public function inject(Container $container)
    {
        // singleton ?
        if ($this->isSingleton && $this->isInstantiated) {
            return $this->instance;
        }

        // create dependency injected instance
        $this->instance = ($this->newInstance)($container);
        $this->isInstantiated = true;

        // @PostConstruct
        if ($this->postConstruct !== null) {
            assert(method_exists($this->instance, $this->postConstruct));
            $this->instance->{$this->postConstruct}();
        }

        return $this->instance;
    }

    /**
     * @param MethodArguments $params
     *
     * @return mixed
     */
    public function injectWithArgs(Container $container, array $params)
    {
        // singleton ?
        if ($this->isSingleton && $this->isInstantiated) {
            return $this->instance;
        }

        // create dependency injected instance
        $this->instance = $this->newInstance->newInstanceArgs($container, $params);
        $this->isInstantiated = true;

        // @PostConstruct
        if ($this->postConstruct !== null) {
            assert(method_exists($this->instance, $this->postConstruct));
            $this->instance->{$this->postConstruct}();
        }

        return $this->instance;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope): void
    {
        if ($scope === Scope::SINGLETON) {
            $this->isSingleton = true;
        }
    }

    /**
     * @param PointcutList $pointcuts
     */
    public function weaveAspects(CompilerInterface $compiler, array $pointcuts): void
    {
        $className = (string) $this->newInstance;
        $reflection = new ReflectionClass($className);
        if ($reflection->isFinal() || $reflection->implementsInterface(MethodInterceptor::class) || $reflection->implementsInterface(WeavedInterface::class)) {
            return;
        }

        $bind = new AopBind();
        $bind->bind($className, $pointcuts);
        if (! $bind->getBindings()) {
            return;
        }

        $class = $compiler->compile($className, $bind);
        $this->newInstance->weaveAspects($class, $bind);
    }

    /** @inheritDoc */
    public function accept(VisitorInterface $visitor)
    {
        return $visitor->visitDependency(
            $this->newInstance,
            $this->postConstruct,
            $this->isSingleton
        );
    }

    public function isSingleton(): bool
    {
        return $this->isSingleton;
    }
}
