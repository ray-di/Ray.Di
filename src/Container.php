<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Ray\Aop\Compiler;
use Ray\Aop\Pointcut;
use Ray\Di\Exception\Unbound;
use Ray\Di\Exception\Untargeted;

final class Container
{
    /**
     * @var DependencyInterface[]
     */
    private $container = [];

    /**
     * @var Pointcut[]
     */
    private $pointcuts = [];

    public function __sleep()
    {
        return ['container', 'pointcuts'];
    }

    /**
     * Add binding to container
     *
     * @param Bind $bind
     */
    public function add(Bind $bind)
    {
        $dependency = $bind->getBound();
        $dependency->register($this->container, $bind);
    }

    /**
     * Add Pointcut to container
     *
     * @param Pointcut $pointcut
     */
    public function addPointcut(Pointcut $pointcut)
    {
        $this->pointcuts[] = $pointcut;
    }

    /**
     * Return instance by interface + name(interface namespace)
     *
     * @return mixed
     */
    public function getInstance(string $interface, string $name)
    {
        return $this->getDependency($interface . '-' . $name);
    }

    /**
     * Return dependency injected instance
     *
     * @throws Unbound
     *
     * @return mixed
     */
    public function getDependency(string $index)
    {
        if (! isset($this->container[$index])) {
            throw $this->unbound($index);
        }
        $dependency = $this->container[$index];

        return $dependency->inject($this);
    }

    /**
     * Rename existing dependency interface + name
     */
    public function move(string $sourceInterface, string $sourceName, string $targetInterface, string $targetName)
    {
        $sourceIndex = $sourceInterface . '-' . $sourceName;
        if (! isset($this->container[$sourceIndex])) {
            throw $this->unbound($sourceIndex);
        }
        $targetIndex = $targetInterface . '-' . $targetName;
        $this->container[$targetIndex] = $this->container[$sourceIndex];
        unset($this->container[$sourceIndex]);
    }

    /**
     * Return Unbound exception
     *
     * @param string $index {interface}-{bind name}
     *
     * @return Untargeted | Unbound
     */
    public function unbound(string $index)
    {
        list($class, $name) = explode('-', $index);
        if (class_exists($class) && ! (new \ReflectionClass($class))->isAbstract()) {
            return new Untargeted($class);
        }

        return new Unbound("{$class}-{$name}");
    }

    /**
     * Return container
     *
     * @return DependencyInterface[]
     */
    public function getContainer() : array
    {
        return $this->container;
    }

    /**
     * Return pointcuts
     *
     * @return Pointcut[]
     */
    public function getPointcuts() : array
    {
        return $this->pointcuts;
    }

    /**
     * Merge container
     */
    public function merge(self $container)
    {
        $this->container += $container->getContainer();
        $this->pointcuts = array_merge($this->pointcuts, $container->getPointcuts());
    }

    /**
     * Weave aspects to all dependency in container
     */
    public function weaveAspects(Compiler $compiler)
    {
        foreach ($this->container as $dependency) {
            if (! $dependency instanceof Dependency) {
                continue;
            }
            $dependency->weaveAspects($compiler, $this->pointcuts);
        }
    }

    /**
     * Weave aspect to single dependency
     */
    public function weaveAspect(Compiler $compiler, Dependency $dependency) : self
    {
        $dependency->weaveAspects($compiler, $this->pointcuts);

        return $this;
    }
}
