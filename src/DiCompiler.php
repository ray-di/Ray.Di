<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Compiler;
use Ray\Di\Exception;

class DiCompiler implements InstanceInterface, \Serializable
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
     * @var DiCompiler
     */
    protected $compiler;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param InjectorInterface $injector
     */
    public function __construct(InjectorInterface $injector, CompileLogger $logger)
    {
        $this->injector = $injector;
        $logger->setConfig($injector->getContainer()->getForge()->getConfig());
        $this->logger = $logger;
    }

    /**
     * @param string $classd
     *
     * @return $this
     */
    public function compile($class)
    {
        $this->injector->setLogger($this->logger);
        $this->injector->getInstance($class);
        $this->classMap[$class] = $this->logger->getLashHash();

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

    public function serialize()
    {
          $serialized = serialize(
            [
                $this->classMap,
                $this->logger,
                $this->compiler
            ]
        );

        return $serialized;
    }

    public function unserialize($serialized)
    {
        list(
            $this->classMap,
            $this->logger,
            $this->compiler
        ) = unserialize($serialized);
    }
}
