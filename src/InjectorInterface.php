<?php
/**
 *
 * This file is part of the Aura Project for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Di;

/**
 *
 * Defines the interface for dependency injector.
 *
 * @package Aura.Di
 *
 * @ImplemetedBy("Aura\Di\Injector")
 */
interface InjectorInterface
{
    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param AbstractModule $module
     */
    public function __construct(ContainerInterface $container, BinderInterface $bind);

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
    public function getInstance($class, AbstractModule $module = null, array $params = null);
}