<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

abstract class AbstractModule
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param AbstractModule $module
     */
    public function __construct(
        AbstractModule $module = null
    ) {
        if (! $this->container) {
            $this->container = new Container;
        }
        $this->configure();
        if ($module) {
            $this->container->merge($module->getContainer());
        }
    }

    abstract protected function configure();

    /**
     * @param string $interface
     *
     * @return Bind
     */
    protected function bind($interface = '')
    {
        $bind = new Bind($this->container, $interface);

        return $bind;
    }

    /**
     * @param AbstractModule $module
     */
    public function install(AbstractModule $module)
    {
        $this->container->merge($module->getContainer());
    }

    /**
     * @param AbstractModule $module
     */
    public function overrideInstall(AbstractModule $module)
    {
        $module->getContainer()->merge($this->container);
        $this->container = $module->getContainer();
    }

    /**
     * @param string $interface
     * @param string $name
     *
     * @return mixed
     */
    public function requestInjection($interface, $name)
    {
        $instance = $this->container->getInstance($interface, $name);

        return $instance;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
