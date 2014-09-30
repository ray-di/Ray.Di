<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Aura\Di\ConfigInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Ray\Di\Exception\Compile;

final class DiCompiler implements InstanceInterface, \Serializable
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @var AbstractCompilationLogger
     */
    private $logger;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * [callable $moduleProvider, Cache $cache, $cacheKey, $tmpDir]
     *
     * @var array
     */
    private static $args;

    /**
     * @param InjectorInterface          $injector
     * @param AbstractCompilationLogger  $logger
     * @param Cache                      $cache
     * @param string                     $cacheKey
     */
    public function __construct(
        InjectorInterface $injector,
        AbstractCompilationLogger $logger,
        Cache $cache,
        $cacheKey
    ) {
        $logger->setConfig($injector->getContainer()->getForge()->getConfig());
        $injector->setLogger($logger);
        $this->injector = $injector;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Return di compiler
     *
     * @param callable $moduleProvider
     * @param Cache    $cache
     * @param string   $cacheKey
     *
     * @return mixed|DiCompiler
     */
    public static function create(callable $moduleProvider, Cache $cache, $cacheKey, $tmpDir)
    {
        (new Locator)->setLogger(new CompilationLogger(new Logger));
        self::$args = func_get_args();
        if ($cache->contains($cacheKey)) {
            (new AopClassLoader)->register($tmpDir);
            $diCompiler = $cache->fetch($cacheKey);

            return $diCompiler;
        }
        $diCompiler = self::createInstance($moduleProvider, $cache, $cacheKey, $tmpDir);

        return $diCompiler;
    }

    /**
     * @param callable $moduleProvider
     * @param Cache    $cache
     * @param string   $cacheKey
     * @param string   $tmpDir
     *
     * @return DiCompiler
     */
    private static function createInstance($moduleProvider, Cache $cache, $cacheKey, $tmpDir)
    {
        $config = new Config(new Annotation(new Definition, new AnnotationReader));
        $logger = (new Locator)->getLogger();
        if (! $logger) {
            $logger = new CompilationLogger(new Logger);
        }
        $logger->setConfig($config);
        $injector = self::createInjector($moduleProvider, $tmpDir, $config, $logger);
        $diCompiler = new DiCompiler($injector, $logger, $cache, $cacheKey);
        $injector->setDiCompiler($diCompiler);

        return $diCompiler;
    }

    /**
     * @param callable $moduleProvider
     * @param string   $tmpDir
     * @param Config   $config
     * @param Logger   $logger
     *
     * @return Injector
     */
    private static function createInjector(
        callable $moduleProvider,
        $tmpDir,
        ConfigInterface $config = null,
        LoggerInterface $logger = null
    ) {
        $injector = (new InjectorFactory)
            ->setConfig($config)
            ->setLogger($logger)
            ->newInstance([$moduleProvider()], new ArrayCache, $tmpDir);

        return $injector;
    }

    /**
     * Compile fluent interface
     *
     * @param string $class
     *
     * @return self
     */
    public function compile($class)
    {
        $instance = $this->injector->getInstance($class);
        $this->logger->setMapRef($class);
        $definition = $this->injector->getDefinition();
        /** @var $definition BoundDefinition */
        if ($definition->isSingleton === true) {
            $key = $this->logger->getSingletonKey($definition);
            $this->logger->setSingletonInstance($key, $instance);
        }

        $this->cache->save($this->cacheKey, $this);

        return $instance;
    }

    /**
     * Get instance from container / injector
     *
     * @param string $class The class to instantiate.
     *
     * @return object
     */
    public function getInstance($class)
    {
        if (! $this->logger->isSetMapRef($class)) {
            $instance = $this->recompile($class);
            return $instance;
        }
        // error_log(sprintf('ray/di.get     class:%s', $class));
        $instance = $this->getInstanceSafe($this->logger, $class);

        return $instance;
    }

    /**
     * @param CompilationLogger $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->injector->setLogger($logger);
    }

    /**
     * @param string $class
     *
     * @return object
     */
    private function recompile($class)
    {
        if ($this->logger) {
            (new Locator)->setLogger($this->logger);
        }
        $diCompiler = $this->injector ? $this : call_user_func_array([$this, 'createInstance'], self::$args);
        $instance = $diCompiler->compile($class);

        return $instance;
    }

    /**
     * Retry get instance if fail
     *
     * this is defensive implementation just in case.
     *
     * @param InstanceInterface $injector
     * @param string            $class
     *
     * @return object
     */
    private function getInstanceSafe(InstanceInterface $injector, $class)
    {
        try {
            $instance = $injector->getInstance($class);
        } catch (Compile $e) {
            // @codeCoverageIgnoreStart
            error_log(sprintf('ray/di.retry class:%s catch:%s exception:%s', $class, __METHOD__, (string) $e));
            list($provider, $tmpDir) = [self::$args[0], self::$args[3]];
            $injector = self::createInjector($provider, $tmpDir);
            $instance = $injector->getInstance($class);
            // @codeCoverageIgnoreEnd
        }

        return $instance;
    }

    /**
     * Destroy injector for runtime
     *
     * @return string
     */
    public function serialize()
    {
          $serialized = serialize(
              [
                $this->logger,
                $this->cache,
                $this->cacheKey
              ]
          );

        return $serialized;
    }

    public function unserialize($serialized)
    {
        list(
            $this->logger,
            $this->cache,
            $this->cacheKey
        ) = unserialize($serialized);
    }

    public function __toString()
    {
        return (string) $this->logger;
    }
}
