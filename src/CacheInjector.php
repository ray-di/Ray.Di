<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Cache\CacheProvider;
use Ray\Di\Exception\NoInjectorReturn;
use Ray\Di\ClassLoaderInterface;

/**
 * Injector with cache container.
 *
 *  - Auto loading for weaved class
 *  - Notify PreShutdown
 */
class CacheInjector implements InstanceInterface
{
    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cache;

    /**
     * @var callable
     */
    private $injector;

    /**
     * @var callable
     */
    private $initialization;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var ClassLoaderInterface
     */
    private $classLoader;

    /**
     * @param callable      $injector       = function () {return Injector::create([new Module])};
     * @param callable      $initialization = function ($instance, InjectorInterface $injector) {};
     * @param string        $namespace      cache namespace
     * @param CacheProvider $cache
     */
    public function __construct(
        callable $injector,
        callable $initialization,
        $namespace,
        CacheProvider $cache,
        ClassLoaderInterface $classLoader = null
    ) {
        $this->injector = $injector;
        $this->initialization = $initialization;
        $this->namespace = $namespace;
        $this->cache = $cache;
        $cache->setNamespace($namespace);
        $this->cache = $cache;
        $this->classLoader = $classLoader ?: new AopClassLoader;
    }

    /**
     * Return injected instance using cache
     *
     * @param string $class class or interface
     *
     * @return object
     */
    public function getInstance($class)
    {
        $key = $this->namespace . $class;
        $instance= $this->cache->contains($key) ?
            $this->cachedInstance($class, $key) :
            $this->createInstance($class, $key);

        return $instance;
    }

    /**
     * Return cached injected instance
     *
     * @param string $class
     * @param string $key
     *
     * @return array
     */
    private function cachedInstance($class, $key)
    {
        $classDir = $this->cache->fetch($key);
        $this->classLoader->register($classDir);

        $instance = $this->cache->fetch("{$key}{$class}");

        return $instance;
    }

    /**
     * Return injected instance and $preDestroy
     *
     * @param string $class
     * @param string $key
     *
     * @return array
     * @throws Exception\NoInjectorReturn
     */
    private function createInstance($class, $key)
    {
        $injector = call_user_func($this->injector);
        if (! $injector instanceof InjectorInterface) {
            throw new NoInjectorReturn;
        }
        /** @var $injector Injector */
        $aopFileDir = $injector->getAopClassDir();
        /** @var $injector Injector */
        $this->removeAopFiles($aopFileDir);
        $instance = $injector->getInstance($class);
        $this->cache->save($key, $aopFileDir);
        $this->cache->save("{$key}{$class}", $instance);

        // post injection
        call_user_func_array($this->initialization, [$instance, $injector]);

        return $instance;
    }

    /**
     * Clear generated aop files
     *
     * @param string $dir
     */
    private function removeAopFiles($dir)
    {
        foreach (glob($dir . '/*Aop.php') as $file) {
            unlink($file);
        }
    }
}
