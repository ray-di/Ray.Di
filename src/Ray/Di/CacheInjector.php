<?php

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use SplObjectStorage;

/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
class CacheInjector
{
    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cache;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var Callable
     */
    private $injector;

    /**
     * @var Callable
     */
    private $init;

    /**
     * @var SplObjectStorage
     */
    private $preDestroy;

    /**
     * @param callable $module   return module
     * @param null     $tmpDir   aop file dir
     * @param Cache    $cache    cache
     * @param callable $logger   injection logger
     * @param callable $injector injector
     */
    public function __construct(
        Callable $module = null,
        $tmpDir = null,
        Cache $cache = null,
        Callable $logger = null,
        Callable $injector = null
    ) {
        $this->module = $module ? : function () {
            return new EmptyModule;
        };
        $this->tmpDir = $tmpDir ? : sys_get_temp_dir();
        $this->cache = $cache ? : new FilesystemCache($this->tmpDir);
        $this->logger = $logger;
        $this->injector = $injector ? : function () {
            $module = $this->module;

            return new Injector(new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader)))), $module(
                ), new Bind, new Compiler($this->tmpDir));
        };
    }

    /**
     * Notify preDestroy method
     */
    public function __destruct()
    {
        if ($this->preDestroy instanceof SplObjectStorage) {
            $this->notifyPreShutdown($this->preDestroy);
        }
    }

    /**
     * Set initialization process
     *
     * This $init closure is called after first injection.
     *
     * $init($this->injector, $instance)
     *
     * @param callable $init
     *
     * @return self
     */
    public function setInit(Callable $init)
    {
        $this->init = $init;

        return $this;
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
        if ($this->cache->contains($class)) {
            list($instance, $preDestroy) = $this->cache->fetch($class);
            $this->preDestroy = $preDestroy;

            return $instance;
        }
        $injector = $this->injector;
        $injector = $injector();
        /** @var $injector InjectorInterface */
        $instance = $injector->getInstance($class);
        $preDestroy = $injector->getPreDestroyObjects();
        $this->cache->save($class, [$instance, $preDestroy]);
        if ($this->init) {
            $init = $this->init;
            $init($injector, $instance);
        }

        return $instance;
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
