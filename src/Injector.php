<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Compiler;

class Injector implements InjectorInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param AbstractModule $module
     * @param string         $classDir
     */
    public function __construct(AbstractModule $module = null, $classDir = null)
    {
        $classDir = $classDir ?: sys_get_temp_dir();
        $this->container =  $module ? $module->getContainer() : new Container;
        $this->container->weaveAspects(new Compiler($classDir));

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
