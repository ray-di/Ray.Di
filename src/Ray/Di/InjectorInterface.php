<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Cache\Cache;

/**
 * Defines the interface for dependency injector.
 *
 * @package Ray.Di
 *
 */
interface InjectorInterface
{
    /**
     * Injector builder
     *
     * @param       array AbstractModule[] $modules
     * @param Cache $cache
     *
     * @return Injector
     */
    public static function create(array $modules = [], Cache $cache = null);

    /**
     * Creates and returns a new instance of a class using 'module,
     * optionally with overriding params.
     *
     * @param string $class          The class to instantiate.
     * @param array  $params         An associative array of override parameters where
     *                               the key the name of the constructor parameter and the value is the
     *                               parameter value to use.
     *
     * @return object
     */
    public function getInstance($class, array $params = null);

    /**
     * Return container
     *
     * @return Container;
     */
    public function getContainer();

    /**
     * Return module
     *
     * @return AbstractModule
     */
    public function getModule();

    /**
     * Set Logger
     *
     * @param LoggerInterface $logger
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * Get Logger
     *
     * @return LoggerInterface
     */
    public function getLogger();

    /**
     * Set module
     *
     * @param AbstractModule $module
     *
     * @return self
     */
    public function setModule(AbstractModule $module);

    /**
     * Set module for module builtin injector
     *
     * @param AbstractModule $module
     *
     * @return self
     */
    public function setSelfInjectorModule(AbstractModule $module);

    /**
     * Set cache adapter
     *
     * @param Cache $cache
     *
     * @return self
     */
    public function setCache(Cache $cache);

    /**
     * Return PreDestroyObject container
     *
     * @return \SplObjectStorage
     */
    public function getPreDestroyObjects();
}
