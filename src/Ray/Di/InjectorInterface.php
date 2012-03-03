<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Defines the interface for dependency injector.
 *
 * @package Ray.Di
 *
 * @ImplementedBy("Ray\Di\Injector")
 */
interface InjectorInterface
{
    /**
     * Creates and returns a new instance of a class using 'module,
     * optionally with overriding params.
     *
     * @param string $class The class to instantiate.
     * @param AbstractModule $module Binding configuration module
     * @param array $params An associative array of override parameters where
     * the key the name of the constructor parameter and the value is the
     * parameter value to use.
     *
     * @return object
     *
     */
    public function getInstance($class, array $params = null);

    /**
     * Return container
     *
     * @return Ray\Di\Container;
     */
    public function getContainer();
}