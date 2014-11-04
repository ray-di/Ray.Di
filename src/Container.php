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
     * @param Bind $bind
     */
    public function add(Bind $bind)
    {
        $dependency = $bind->getBound();
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
            list($class, $name) = explode('-', $index);
            throw new Unbound("interface:{$class} name:{$name}");
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
        $dependency = (new Bind($this, $class))->to($class);
        $this->add($dependency);

        return $this->getDependency($class . '-' . $name);
    }

    /**
     * @return InjectInterface[]
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function getPointcuts()
    {
        return $this->pointcuts;
    }

    /**
     * @param Container $container
     */
    public function merge(Container $container)
    {
        $this->container = $this->container + $container->getContainer();
        $this->pointcuts = $this->pointcuts + $container->getPointcuts();
    }

    /**
     * @param Compiler $compiler
     */
    public function weaveAspects(Compiler $compiler)
    {
        foreach ($this->container as $dependency) {
            if (! $dependency instanceof Dependency) {
                continue;
            }
            /** @var $dependency Dependency */
            $dependency->weaveAspects($compiler, $this->pointcuts);
        }
    }

    /**
     * @param Compiler   $compiler
     * @param Dependency $dependency
     *
     * @return $this
     */
    public function weaveAspect(Compiler $compiler, Dependency $dependency)
    {
        $dependency->weaveAspects($compiler, $this->pointcuts);

        return $this;
    }

    public function __sleep()
    {
        return ['container'];
    }
}
