<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\Di\ImplementedBy;
use Doctrine\Common\Cache\Cache;

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
     * @param string         $class  The class to instantiate.
     * @param array          $params An associative array of override parameters where
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
     * @return void
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * Set cache adapter
     *
     * @param Cache $cache
     *
     * @return self
     */
    public function setCache(Cache $cache);
}
