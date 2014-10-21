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
     * @var AbstractModule
     */
    private $module;

    /**
     * @param AbstractModule $module
     */
    public function __construct(AbstractModule $module = null)
    {
        $this->module = $module ?: new NullModule;
    }

    /**
     * @param string $interface
     * @param string $name
     *
     * @return mixed
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        $instance = $this->module->requestInjection($interface, $name);

        return $instance;
    }
}
