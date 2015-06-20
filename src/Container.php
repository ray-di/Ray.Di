<?php

/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Ray\Aop\Compiler;
use Ray\Aop\Pointcut;
use Ray\Di\Exception\Unbound;
use Ray\Di\Exception\Untargetted;

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
     *
     * @throws Unbound
     */
    public function getDependency($index)
    {
        if (! isset($this->container[$index])) {
            throw $this->unbound($index);
        }
        $dependency = $this->container[$index];
        $instance = $dependency->inject($this);

        return $instance;
    }

    /**
     * Rename existing dependency interface + name
     *
     * @param string $sourceInterface
     * @param string $sourceName
     * @param string $targetInterface
     * @param string $targetName
     */
    public function move($sourceInterface, $sourceName, $targetInterface, $targetName)
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
     * @param string $index {interface}-{bind name}
     *
     * @return Untargetted | Unbound
     */
    public function unbound($index)
    {
        list($class, $name) = explode('-', $index);

        $depStack = $this->getDepStack();
        array_shift($depStack); // we skip the first item, as that is the dep that is missing
        
        $dependencyChain = [];
        foreach ($depStack as list($depIndex, $dependency)) {
            $dependencyChain[] = ' - required by: '.$dependency->getDebugInfo();
        }

        // formatting:
        if (!empty($dependencyChain)) {
            $dependencyHelperTrace = "\n" . implode("\n", $dependencyChain) . "\n";
        } else {
            $dependencyHelperTrace = '';
        }

        if (class_exists($class) && ! (new \ReflectionClass($class))->isAbstract()) {
            return new Untargetted("{$class}{$dependencyHelperTrace}");
        }
        return new Unbound("{$class} (bind namespace: \"{$name}\"):{$dependencyHelperTrace}");
    }

    /**
     * @return array[] An array of 2-element arrays where first element is the
     * dependency index and the second is the actual dependency object (or null if not found)
     */
    protected function getDepStack()
    {
        $depStack = [];
        foreach (debug_backtrace() as $item) {
            if ($item['function'] === 'getDependency' && $item['class'] === Container::class) {
                $index = $item['args'][0];
                $containerObj = $item['object'];

                $containerArray = $containerObj->getContainer();
                if (isset($containerArray[$index])) {
                    $depStack[] = [$index, $containerArray[$index]];
                } else {
                    $depStack[] = [$index, null];
                }
            }
        }
        return $depStack;
    }

    /**
     * @return DependencyInterface[]
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \Ray\Aop\Pointcut[]
     */
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
        $this->pointcuts = array_merge($this->pointcuts, $container->getPointcuts());
    }

    /**
     * Weave aspects to all dependency in container
     *
     * @param Compiler $compiler
     */
    public function weaveAspects(Compiler $compiler)
    {
        foreach ($this->container as $dependency) {
            if (! $dependency instanceof Dependency) {
                continue;
            }
            /* @var $dependency Dependency */
            $dependency->weaveAspects($compiler, $this->pointcuts);
        }
    }

    /**
     * Weave aspect to single dependency
     *
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
        return ['container', 'pointcuts'];
    }
}
