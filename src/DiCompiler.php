<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;

final class DiCompiler implements InstanceInterface, \Serializable
{
    /**
     * @var array
     */
    private $classMap = [];

    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @var CompileLoggerInterface
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
     * @param InjectorInterface $injector
     * @param CompileLogger     $logger
     */
    public function __construct(
        InjectorInterface $injector,
        CompileLoggerInterface $logger,
        Cache $cache = null,
        $cacheKey = __CLASS__
    ) {
        $this->injector = $injector;
        $logger->setConfig($injector->getContainer()->getForge()->getConfig());
        $this->logger = $logger;
        $this->cache = $cache ?: new ArrayCache;
        $this->cacheKey = $cacheKey;
    }

    public static function create(callable $injector, Cache $cache, $cacheKey = __METHOD__)
    {
        if ($cache->contains($cacheKey)) {
            return $cache->fetch($cacheKey);
        }
        $compiler = new self($injector(), new CompileLogger(new Logger), $cache, $cacheKey);

        return $compiler;
    }

    /**
     * @param string $class
     *
     * @return self
     */
    public function compile($class)
    {
        $this->injector->setLogger($this->logger);
        $this->injector->getInstance($class);
        $this->classMap = $this->logger->setClassMap($this->classMap, $class);
        $this->cache->save($this->cacheKey, $this);
        return $this;
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
        if (! isset($this->classMap[$class])) {
            $this->compile($class);
        }
        $hash = $this->classMap[$class];
        $instance = $this->logger->newInstance($hash);

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
                $this->classMap,
                $this->logger
              ]
          );

        return $serialized;
    }

    public function unserialize($serialized)
    {
        list(
            $this->classMap,
            $this->logger
        ) = unserialize($serialized);
    }
}
