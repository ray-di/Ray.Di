<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind;
use Aura\Di\ConfigInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

final class CompilationLogger implements CompilationLoggerInterface, InstanceInterface, \Serializable
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DependencyContainer
     */
    private $dependencyContainer;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var string
     */
    private $log = '';

    /**
     * @var array
     */
    private $classMap = [];

    /**
     * @param LoggerInterface $logger
     *
     * @Inject
     * @Named("logger")
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->dependencyContainer = new DependencyContainer($this);
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function log($class, array $params, array $setters, $instance, Bind $bind)
    {
        if ($instance instanceof DependencyProvider) {
            $this->dependencyContainer->attachProvider($instance);

            return;
        }
        $this->logger->log($class, $params, $setters, $instance, $bind);
        // @PostConstruct
        list(,,$definition) = $this->config->fetch($class);
        $postConstructMethod = $definition['PostConstruct'];
        $this->dependencyContainer->attachFactory($instance, $params, $setters, $postConstructMethod);
    }

    /**
     * @param string $class
     *
     * @return object
     */
    public function getInstance($class)
    {
        $ref = $this->classMap[$class];
        return $this->newInstance($ref);
    }

    /**
     * @param string $ref
     *
     * @return object
     * @throws Exception\Compile
     */
    public function newInstance($ref)
    {
        return $this->dependencyContainer->newInstance($ref);
    }

    /**
     * {@inheritdoc}
     */
    public function setMapRef($class)
    {
        if (isset($this->classMap[$class])) {

            return;
        }
        $factory = $this->dependencyContainer->pop();
        $this->classMap[$class] = (string) $factory;
        $log = sprintf(
            'ray/di.map     ref:%s class:%s',
            $factory,
            $class
        );
        $this->errorLog($log);
    }

    /**
     * {@inheritdoc}
     */
    public function getMapRef($class)
    {
        return $this->classMap[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function isSetMapRef($class)
    {
        return isset($this->classMap[$class]);
    }

    /**
     * @param string $log
     *
     * Uncomment for error_log to log
     */
    private function errorLog($log)
    {
        error_log($log);
        $this->log .= $log . PHP_EOL;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $log = '';
        foreach ($this->dependencyContainer as $num => $item) {
            $type = explode('\\', get_class($item))[2];
            $log .= sprintf("num:%s type:%s name:%s\n", $num, $type, $item->getName());
        }

        return $log;
    }

    public function serialize()
    {
        $serialized = serialize(
            [
                $this->classMap,
                $this->dependencyContainer
            ]
        );

        return $serialized;
    }

    public function unserialize($serialized)
    {
        $this->dependencyContainer = new DependencyContainer($this);
        list(
            $this->classMap,
            $this->dependencyContainer
        ) = unserialize($serialized);
    }
}
