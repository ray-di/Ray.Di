<?php

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ApcCache;
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
    private $aopDir;

    /**
     * @var Callable
     */
    private $injector;

    /**
     * @var Callable
     */
    private $init;

    /**
     * @param callable $module   return module
     * @param null     $aopDir   aop file dir
     * @param Cache    $cache    cache
     * @param callable $logger   injection logger
     * @param callable $injector injector
     */
    public function __construct(
        Callable $module = null,
        $aopDir = null,
        Cache $cache = null,
        Callable $logger = null,
        Callable $injector = null
    ) {
        $this->module = $module ? : function () {
            return new EmptyModule;
        };
        $this->aopDir = $aopDir ? : sys_get_temp_dir();
        if (is_null($cache)) {
            $cache = function_exists('apc_fetch') ? new ApcCache : new FilesystemCache($this->aopDir);
        }
        $this->cache = $cache ? : new FilesystemCache($this->aopDir);
        $this->cache = $cache;
        $this->logger = $logger;
        $this->injector = $injector ? : $this->getInjectorClosure();

        $this->registerGeneratedAopFileAutoLoader();
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
        list($instance, $preDestroy) =
            $this->cache->contains($class) ? $this->cache->fetch($class) : $this->createInstance($class);

        register_shutdown_function(
            function () use ($preDestroy) {
                $this->notifyPreShutdown($preDestroy);
            }
        );

        return $instance;
    }

    /**
     * @return callable
     */
    private function getInjectorClosure()
    {
        return function () {
            $module = $this->module;

            return new Injector(new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader)))), $module(
            ), new Bind, new Compiler($this->aopDir));
        };
    }

    /**
     * @return void
     */
    private function registerGeneratedAopFileAutoLoader()
    {
        spl_autoload_register(
            function ($class) {
                $file = $this->aopDir . DIRECTORY_SEPARATOR . $class . '.php';
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
        $injector = $this->injector;
        $injector = $injector();
        /** @var $injector InjectorInterface */
        $this->setLogger($injector, $this->logger);
        $this->removeAopFiles($this->aopDir);
        $instance = $injector->getInstance($class);
        $preDestroy = $injector->getPreDestroyObjects();
        $this->cache->save($class, [$instance, $preDestroy]);
        if ($this->init) {
            $init = $this->init;
            $init($injector, $instance);
        }

        return [$instance, $preDestroy];
    }

    /**
     * Set injection logger
     *
     * @param InjectorInterface $injector
     * @param callable          $logger
     */
    private function setLogger(InjectorInterface $injector, callable $logger = null)
    {
        if (is_callable($logger)) {
            $logger = $logger();
            /** @var $logger LoggerInterface */
            $injector->setLogger($logger);
        }
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
