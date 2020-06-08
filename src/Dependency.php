<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\Bind as AopBind;
use Ray\Aop\CompilerInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\Pointcut;
use Ray\Aop\WeavedInterface;
use ReflectionClass;
use ReflectionMethod;

final class Dependency implements DependencyInterface
{
    /**
     * @var NewInstance
     */
    private $newInstance;

    /**
     * @var ?string
     */
    private $postConstruct;

    /**
     * @var bool
     */
    private $isSingleton = false;

    /**
     * @var mixed
     */
    private $instance;

    /**
     * @var string
     */
    private $index = '';

    /**
     * @param ReflectionMethod $postConstruct
     */
    public function __construct(NewInstance $newInstance, ReflectionMethod $postConstruct = null)
    {
        $this->newInstance = $newInstance;
        $this->postConstruct = $postConstruct ? $postConstruct->name : null;
    }

    public function __sleep()
    {
        return ['newInstance', 'postConstruct', 'isSingleton'];
    }

    public function __toString()
    {
        return sprintf(
            '(dependency) %s',
            (string) $this->newInstance
        );
    }

    /**
     * {@inheritdoc}
     */
    public function register(array &$container, Bind $bind) : void
    {
        $this->index = $index = (string) $bind;
        $container[$index] = $bind->getBound();
    }

    /**
     * {@inheritdoc}
     */
    public function inject(Container $container)
    {
        // singleton ?
        if ($this->isSingleton === true && $this->instance) {
            return $this->instance;
        }

        // create dependency injected instance
        $this->instance = ($this->newInstance)($container);

        // @PostConstruct
        if ($this->postConstruct) {
            assert(method_exists($this->instance, $this->postConstruct));
            $this->instance->{$this->postConstruct}();
        }

        return $this->instance;
    }

    /**
     * @param array<int, mixed> $params
     *
     * @return mixed
     */
    public function injectWithArgs(Container $container, array $params)
    {
        // singleton ?
        if ($this->isSingleton === true && $this->instance) {
            return $this->instance;
        }

        // create dependency injected instance
        $this->instance = $this->newInstance->newInstanceArgs($container, $params);

        // @PostConstruct
        if ($this->postConstruct) {
            assert(method_exists($this->instance, $this->postConstruct));
            $this->instance->{$this->postConstruct}();
        }

        return $this->instance;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope) : void
    {
        if ($scope === Scope::SINGLETON) {
            $this->isSingleton = true;
        }
    }

    /**
     * @param array<int,Pointcut> $pointcuts
     */
    public function weaveAspects(CompilerInterface $compiler, array $pointcuts) : void
    {
        $class = (string) $this->newInstance;
        /**  @psalm-suppress RedundantConditionGivenDocblockType */
        assert(class_exists($class));
        $isInterceptor = (new ReflectionClass($class))->implementsInterface(MethodInterceptor::class);
        $isWeaved = (new ReflectionClass($class))->implementsInterface(WeavedInterface::class);
        if ($isInterceptor || $isWeaved) {
            return;
        }
        $bind = new AopBind;
        $className = (string) $this->newInstance;
        assert(class_exists($className) || interface_exists((string) $className));
        $bind->bind($className, $pointcuts);
        if (! $bind->getBindings()) {
            return;
        }
        $class = $compiler->compile($className, $bind);
        /** @psalm-suppress ArgumentTypeCoercion */
        $this->newInstance->weaveAspects($class, $bind); // @phpstan-ignore-line
    }
}
