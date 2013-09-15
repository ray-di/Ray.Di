<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 */
namespace Ray\Di;

use Doctrine\Common\Cache\Cache;

/**
 * Defines the interface for dependency injector.
 *
 * @package Ray.Di
 *
 */
interface InjectorInterface extends InstanceInterface
{
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
     * Set module for module builtin injector
     *
     * @param AbstractModule $module
     *
     * @return self
     */
    public function setSelfInjectorModule(AbstractModule $module);

    /**
     * Set module
     *
     * @param AbstractModule $module
     *
     * @return self
     */
    public function setModule(AbstractModule $module);

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
