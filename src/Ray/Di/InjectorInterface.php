<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Aura\Di\ContainerInterface;

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
     * Constructor
     *
     * @param ContainerInterface $container
     * @param AbstractModule $module
     */
    public function __construct(ContainerInterface $container, AbstractModule $module = null);

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
}