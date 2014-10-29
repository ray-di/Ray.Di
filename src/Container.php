<?php

/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Compiler;
use Ray\Aop\Pointcut;
use Ray\Di\Exception\Unbound;

final class Container
{
    /**
     * @var InjectInterface[]
     */
    private $container = [];

    /**
     * @var Pointcut[]
     */
    private $pointcuts = [];

    /**
     * @var \SplObjectStorage
     */
    private $dependencyStorage;

    public function __construct()
    {
        $this->dependencyStorage = new \SplObjectStorage;
    }

    /**
     * @param Bind $bind
     */
    public function add(Bind $bind)
    {
        $dependency = $bind->getBound();
        if ($dependency instanceof Dependency) {
            $this->dependencyStorage->attach($dependency);
        }
        $this->container[(string) $bind] = $dependency;
    }

    /**
     * @param Pointcut $pointcut
     */
    public function addPointcut(Pointcut $pointcut)
    {
        $this->pointcuts[] = $pointcut;
        foreach ($pointcut->interceptors as &$interceptor) {
            $bind = (new Bind($this, $interceptor))->to($interceptor)->in(Scope::SINGLETON);
            $this->add($bind);
        }
    }

    /**
     * @param string $interface
     * @param string $name
     *
     * @return mixed
     */
    public function getInstance($interface, $name)
    {
        return $this->getDependency($interface . '-' . $name);
    }

    /**
     * Return dependency injected instance
     *
     * @param string $index
     *
     * @return mixed
     * @throws Unbound
     */
    public function getDependency($index)
    {
        if (! isset($this->container[$index])) {
            return $this->getConcreteClass($index);
        }
        $dependency = $this->container[$index];
        $instance = $dependency->inject($this);

        return $instance;
    }

    /**
     * @param string $index
     *
     * @return mixed
     * @throws Exception\NotFound
     * @throws Unbound
     */
    private function getConcreteClass($index)
    {
        list($class, $name) = explode('-', $index);
        if (! class_exists($class)) {
            throw new Unbound("interface:{$class} name:{$name}");
        }
        // binding on demand
        $this->add((new Bind($this, $class))->to($class));

        return $this->getDependency($class . '-' . $name);
    }

    /**
     * @return InjectInterface[]
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Container $container
     */
    public function merge(Container $container)
    {
        $this->container = $this->container + $container->getContainer();
    }

    /**
     * @param Compiler $compiler
     */
    public function weaveAspects(Compiler $compiler)
    {
        foreach ($this->dependencyStorage as $dependency) {
            /** @var $dependency Dependency */
            $dependency->weaveAspects($compiler, $this->pointcuts);
        }
    }

    public function __sleep()
    {
        return ['container'];
    }
}
