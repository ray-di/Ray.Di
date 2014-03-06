<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

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
     * @param InjectorInterface $injector
     * @param CompileLogger     $logger
     */
    public function __construct(InjectorInterface $injector, CompileLoggerInterface $logger)
    {
        $this->injector = $injector;
        $logger->setConfig($injector->getContainer()->getForge()->getConfig());
        $this->logger = $logger;
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
