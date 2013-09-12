<?php

/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\Exception\LogicException;
use Ray\Aop\Compiler;
use Ray\Di\Exception\NoInjectorReturn;
use SplObjectStorage;

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
     * @param callable $injector       = function() {return Injector::create([new Module])};
     * @param callable $initialization = function($instance, InjectorInterface $injector){};
     * @param string   $namespace      cache namespace
     * @param Cache    $cache
     */
    public function __construct(
        callable $injector,
        callable $initialization,
        $namespace,
        Cache $cache
    ) {
        $this->injector = $injector;
        $this->initialization = $initialization;
        $this->namespace = $namespace;
        $this->cache = $cache;
        $cache->setNamespace($namespace);
        $this->cache = $cache;
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
        list($instance, $preDestroy)= $this->cache->contains($key) ?
            $this->cachedInstance($class, $key) :
            $this->createInstance($class, $key);

        register_shutdown_function(
            function () use ($preDestroy) {
                // @codeCoverageIgnoreStart
                $this->notifyPreShutdown($preDestroy);
            }
            // @codeCoverageIgnoreEnd
        );

        return $instance;
    }

    /**
     * Return cached injected instance
     *
     * @param $class
     * @param $key
     *
     * @return array
     */
    private function cachedInstance($class, $key)
    {
        $classDir = $this->cache->fetch($key);
        $this->registerAopFileLoader($classDir);
        if ($this->cache->contains($key . $class)) {
            list($instance, $preDestroy) = $this->cache->fetch("{$key}{$class}");
        }

        return [$instance, $preDestroy];
    }

    /**
     * Return injected instance and $preDestroy
     *
     * @param $class
     * @param $key
     *
     * @return array [object $instance, SplObjectStorage $preDestroy]
     * @throws Exception\CachedInjector
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
        $preDestroy = $injector->getPreDestroyObjects();
        $this->cache->save($key, $aopFileDir);
        $this->cache->save("{$key}{$class}", [$instance, $preDestroy]);

        // post injection
        call_user_func_array($this->initialization, [$instance, $injector]);
        return [$instance, $preDestroy];
    }

    /**
     * Register generated aop file auto loader
     *
     * @param $classDir
     */
    private function registerAopFileLoader($classDir)
    {
        spl_autoload_register(
            function ($class) use ($classDir) {
                $file = $classDir . DIRECTORY_SEPARATOR . $class . '.php';
                if (file_exists($file)) {
                    include $file;
                }
            }
        );
    }

    /**
     * Clear generated aop files
     *
     * @param $dir
     */
    private function removeAopFiles($dir)
    {
        foreach (glob($dir . '/*Aop.php') as $file) {
            unlink($file);
        }
    }

    /**
     * Notify pre-destroy
     *
     * @param SplObjectStorage $preDestroyObjects
     *
     * @return void
     */
    private function notifyPreShutdown(SplObjectStorage $preDestroyObjects)
    {
        // @codeCoverageIgnoreStart
        $preDestroyObjects->rewind();
        while ($preDestroyObjects->valid()) {
            $object = $preDestroyObjects->current();
            $method = $preDestroyObjects->getInfo();
            $object->$method();
            $preDestroyObjects->next();
        }
    }
    // @codeCoverageIgnoreEnd
}
