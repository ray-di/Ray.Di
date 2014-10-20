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
     * @param string $index
     *
     * @return mixed
     * @throws Unbound
     */
    public function getDependency($index)
    {
        if (! isset($this->container[$index])) {
            throw new Unbound($index);
        }
        $dependency = $this->container[$index];
        $instance = $dependency->inject($this);

        return $instance;
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
