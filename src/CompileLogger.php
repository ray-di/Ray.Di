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

class CompileLogger implements LoggerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $ref;

    /**
     * @var DependencyFactory[]
     */
    protected $instanceContainer = [];

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var \SplObjectStorage
     */
    private $objectStorage;

    /**
     * @param LoggerInterface $logger
     *
     * @Inject
     * @Named("logger")
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->objectStorage = new \SplObjectStorage;
    }

    /**
     * @param ConfigInterface $config
     *
     * @return self
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }
    /**
     * Log injection
     *
     * @param string $class
     * @param array  $params
     * @param array  $setters
     * @param object $instance
     * @param Bind   $bind
     */
    public function log($class, array $params, array $setters, $instance, Bind $bind)
    {
        if ($instance instanceof DependencyProvider) {
            $this->buildProvider($instance);

            return;
        }
        $this->logger->log($class, $params, $setters, $instance, $bind);
        $this->build($class, $instance, $params, $setters);
    }

    /**
     * @param string $ref
     *
     * @return mixed
     * @throws \LogicException
     */
    public function newInstance($ref)
    {
        if (! isset($this->instanceContainer[$ref])) {
            // @codeCoverageIgnoreStart
            throw new Exception\Compile($ref);
            // @codeCoverageIgnoreEnd
        }
        if ($this->instanceContainer[$ref] instanceof DependencyReference) {
            return $this->instanceContainer[$ref]();
        }
        return $this->instanceContainer[$ref]->newInstance();
    }

    /**
     * @param $object
     *
     * @return string
     */
    public function getObjectHash($object)
    {
        static $cnt = 0;

        if ($this->objectStorage->contains($object)) {
            return $this->objectStorage[$object];
        }
        $cnt++;
        $hash = (string)$cnt;
        $this->objectStorage[$object] = $hash;

        return $hash;
    }

    /**
     * @return string
     */
    public function getLastHash()
    {
        $container = $this->instanceContainer;
        $factory = array_pop($container);

        return (string)$factory;
    }

    /**
     * @param object $instance
     * @param array  $params
     * @param array  $setters
     * @param string $class
     */
    private function build($class, $instance, array $params, array $setters)
    {
        $params = $this->makeParamRef($params);
        foreach ($setters as &$methodPrams) {
            $methodPrams = $this->makeParamRef($methodPrams);
        }
        $dependencyFactory = new DependencyFactory($instance, $params, $setters, $this);
        list(,,$definition) = $this->config->fetch($class);
        // @PostConstruct
        $postConstructMethod = $definition['PostConstruct'];
        $dependencyFactory->setPostConstruct($postConstructMethod);
        // aop ?
        if (isset($instance->rayAopBind)) {
            $interceptors = $this->buildInterceptor($instance);
            $dependencyFactory->setInterceptors($interceptors);
        }
        $this->add($dependencyFactory);
    }

    /**
     * @param $instance
     *
     * @return array
     */
    private function buildInterceptor($instance)
    {
        $boundInterceptors = (array)$instance->rayAopBind; // 'methodName' => methodInterceptors[]
        foreach ($boundInterceptors as $methodInterceptors) {
            foreach ($methodInterceptors as &$methodInterceptor) {
                /** @var $methodInterceptor \Ray\Aop\MethodInterceptor */
                $methodInterceptor = $this->getRef($methodInterceptor);
            }
        }

        return $boundInterceptors;
    }

    /**
     * @param DependencyProvider $dependencyProvider
     */
    private function buildProvider(DependencyProvider $dependencyProvider)
    {
        $instanceHash = $this->getObjectHash($dependencyProvider->instance);
        $providerHash = $this->getObjectHash($dependencyProvider->provider);
        $this->instanceContainer[$instanceHash] = new DependencyReference($providerHash, $this);
    }
    /**
     * @param array $params
     *
     * @return array
     */
    private function makeParamRef(array $params)
    {
        foreach ($params as &$param) {
            if (is_object($param)) {
                $param = $this->getRef($param);
            }
            if (is_array($param)) {
                $param = $this->getArray($param);
            }
        }

        return $params;
    }

    /**
     * @param object $instance
     *
     * @return DependencyReference
     */
    private function getRef($instance)
    {
        $hash = $this->getObjectHash($instance);
        return new DependencyReference($hash, $this);
    }

    /**
     * @param array $array
     *
     * @return self
     */
    private function getArray(array $array)
    {
        // @todo
        return $array;
    }

    /**
     * @param DependencyFactory $instance
     */
    private function add($instance)
    {
        $index = (string)$instance;
        $this->instanceContainer[$index] = $instance;
    }

    public function __toString()
    {
        return (string)$this->logger;
    }
}
