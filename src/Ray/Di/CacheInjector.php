<?php

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Aop\Compiler;
use SplObjectStorage;

/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
    private $postInject;

    private $tmpDir;

    /**
     * @param callable $injector
     * @param callable $postInject
     * @param          $key
     * @param Cache    $cache
     * @param          $tmpDir
     *
     * $injector = function() {return Injector::create([new Module]);}
     * $postInject = function($instance, InjectorInterface $injector){};
     */
    public function __construct(
        callable $injector,
        callable $postInject,
        $key,
        Cache $cache,
        $tmpDir
    ) {
        $this->injector = $injector;
        $this->postInject = $postInject;
        $this->cache = $cache;
        $cache->setNamespace($key);
        $this->cache = $cache;
        $this->tmpDir = $tmpDir;
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
        list($instance, $preDestroy, $classDir) =
            $this->cache->contains($class) ?
                $this->cache->fetch($class) :
                $this->createInstance($class);

        $this->registerAopFileLoader($classDir);

        register_shutdown_function(
            function () use ($preDestroy) {
                $this->notifyPreShutdown($preDestroy);
            }
        );

        return $instance;
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
                    /** @noinspection PhpIncludeInspection */
                    include $file;
                }
            }
        );
    }
    /**
     * Return injected instance and $preDestroy
     *
     * @param $class
     *
     * @return array [object $instance, SplObjectStorage $preDestroy]
     */
    private function createInstance($class)
    {
        $injector = call_user_func($this->injector);
        /** @var $injector Injector */
        $this->removeAopFiles($this->tmpDir);
        $instance = $injector->getInstance($class);
        $preDestroy = $injector->getPreDestroyObjects();
        $this->cache->save($class, [$instance, $preDestroy, $injector->getAopClassDir()]);

        // post injection
        call_user_func_array($this->postInject, [$instance, $injector]);

        return [$instance, $preDestroy, $injector->getAopClassDir()];
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
        $preDestroyObjects->rewind();
        while ($preDestroyObjects->valid()) {
            $object = $preDestroyObjects->current();
            $method = $preDestroyObjects->getInfo();
            $object->$method();
            $preDestroyObjects->next();
        }
    }
}
