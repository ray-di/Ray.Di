<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

class Injector implements InjectorInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param AbstractModule $module
     */
    public function __construct(AbstractModule $module = null)
    {
        $this->container =  $module ? $module->getContainer() : new Container;
        // builtin injection
        (new Bind($this->container, InjectorInterface::class))->toInstance($this);
    }

    /**
     * @param string $interface
     * @param string $name
     *
     * @return mixed
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        $instance = $this->container->getInstance($interface, $name);

        return $instance;
    }
}
