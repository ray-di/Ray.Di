<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\Bind as AopBind;
use Ray\Aop\CompilerInterface;
use Ray\Aop\MethodInterceptor;

final class Dependency implements DependencyInterface
{
    /**
     * @var NewInstance
     */
    private $newInstance;

    /**
     * @var null|string
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
    private $index;

    /**
     * @param \ReflectionMethod $postConstruct
     */
    public function __construct(NewInstance $newInstance, \ReflectionMethod $postConstruct = null)
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
    public function register(array &$container, Bind $bind)
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
            $this->instance->{$this->postConstruct}();
        }

        return $this->instance;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
        if ($scope === Scope::SINGLETON) {
            $this->isSingleton = true;
        }
    }

    public function weaveAspects(CompilerInterface $compiler, array $pointcuts)
    {
        $class = (string) $this->newInstance;
        $isInterceptor = (new \ReflectionClass($class))->implementsInterface(MethodInterceptor::class);
        if ($isInterceptor) {
            return;
        }
        $bind = new AopBind;
        $bind->bind((string) $this->newInstance, $pointcuts);
        if (! $bind->getBindings()) {
            return;
        }
        $class = $compiler->compile((string) $this->newInstance, $bind);
        $this->newInstance->weaveAspects($class, $bind);
    }
}
