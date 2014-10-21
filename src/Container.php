<?php

/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\Exception\Unbound;

final class Container
{
    /**
     * @var InjectInterface[]
     */
    private $container = [];

    /**
     * @param Bind $bind
     */
    public function add(Bind $bind)
    {
        $this->container[(string) $bind] = $bind->getBound();
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
            return $this->getOnDemandBoundDependency($index);
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
    private function getOnDemandBoundDependency($index)
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
}
