<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Aura\Di\ConfigInterface;
use Ray\Aop\Bind;
use Ray\Di\Exception\UnknownCompiledObject;

final class CompilationLogger extends AbstractCompilationLogger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DependencyFactory[]
     */
    private $dependencyContainer = [];

    /**
     * @var array
     */
    private $singletonContainer = [];

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var \SplObjectStorage
     */
    private $objectStorage;

    /**
     * @var string
     */
    private $log = '';

    /**
     * @var array
     */
    private $classMap = [];

    /**
     * @var string
     */
    private $lastDependencyFactoryIndex;

    /**
     * @param LoggerInterface $logger
     *
     * @Ray\Di\Di\Inject
     * @Ray\Di\Di\Named("logger")
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->objectStorage = new \SplObjectStorage;
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
    public function log(BoundDefinition $definition, array $params, array $setters, $instance, Bind $bind)
    {
        if ($instance instanceof DependencyProvider) {
            $this->buildProvider($instance, $definition);

            return;
        }
        $this->logger->log($definition, $params, $setters, $instance, $bind);
        $this->build($definition, $instance, $params, $setters);
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
        if (! isset($this->dependencyContainer[$ref])) {
            $this->errorLog((string) $this->log);
            throw new Exception\Compile($ref);
        }

        return $this->dependencyContainer[$ref]->get();
    }


    /**
     * {@inheritdoc}
     */
    public function getObjectIndex($object, BoundDefinition $definition = null)
    {
        if ($this->objectStorage->contains($object)) {
            return $this->objectStorage[$object];
        }
        if (is_null($definition)) {
            if ($object instanceof \Ray\Di\Injector) {
                return 'injector';
            }
            throw new UnknownCompiledObject(get_class($object));
        }
        $hash = "{$definition->class}-{$definition->interface}-{$definition->name}";
        $this->objectStorage[$object] = $hash;
        // object hash logging for debug
        $shortHash = function ($data, $algo = 'CRC32') {
            return strtr(rtrim(base64_encode(pack('H*', sprintf('%u', $algo($data)))), '='), '+/', '-_');
        };
        $log = sprintf(
            'ray/di.install ref:%s class:%s hash:%s',
            $hash,
            get_class($object),
            $shortHash(spl_object_hash($object))
        );
        $this->errorLog($log);

        return $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function setMapRef($class)
    {
        if (isset($this->classMap[$class])) {
            return;
        }
        $this->classMap[$class] = $this->lastDependencyFactoryIndex;
        $log = sprintf(
            'ray/di.map     ref:%s class:%s',
            $this->lastDependencyFactoryIndex,
            $class
        );
        $this->errorLog($log);
    }

    /**
     * {@inheritdoc}
     */
    public function isSetMapRef($class)
    {
        return isset($this->classMap[$class]);
    }

    /**
     * @param BoundDefinition $definition
     * @param object          $instance
     * @param array           $params
     * @param array           $setters
     */
    private function build(BoundDefinition $definition, $instance, array $params, array $setters)
    {
        // constructor parameters
        $params = $this->makeParamRef($params);

        // setter parameters
        foreach ($setters as &$methodPrams) {
            $methodPrams = $this->makeParamRef($methodPrams);
        }

        $dependencyFactory = new DependencyFactory($instance, $params, $setters, $this, $definition);
        list(,,$definition) = $this->config->fetch($definition->class);
        // @PostConstruct
        $postConstructMethod = $definition['PostConstruct'];
        $dependencyFactory->setPostConstruct($postConstructMethod);
        // aop ?
        if (isset($instance->rayAopBind)) {
            $interceptors = $this->buildInterceptor($instance);
            $dependencyFactory->setInterceptors($interceptors);
        }
        $this->setDependencyFactory($dependencyFactory);
        $diLog = $dependencyFactory->getDependencyLog();
        if ($diLog) {
            $this->errorLog('ray/di.depends ' . $diLog);
        }
        $aopLog = $dependencyFactory->getInterceptorLog();
        if ($aopLog) {
            $this->errorLog('ray/di.aspect  ' . $aopLog);
        }
    }

    /**
     * @param DependencyFactory $dependencyFactory
     */
    private function setDependencyFactory(DependencyFactory $dependencyFactory)
    {
        $index = (string) $dependencyFactory;
        $this->dependencyContainer[$index] = $dependencyFactory;
        $this->lastDependencyFactoryIndex = $index;
    }

    /**
     * @param object $instance
     *
     * @return array
     */
    private function buildInterceptor($instance)
    {
        $boundInterceptors = (array) $instance->rayAopBind; // 'methodName' => methodInterceptors[]
        foreach ($boundInterceptors as &$methodInterceptors) {
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
    private function buildProvider(DependencyProvider $dependencyProvider, BoundDefinition $definition)
    {
        $instanceHash = $this->getObjectIndex($dependencyProvider->instance, $definition);
        $providerHash = $this->getObjectIndex($dependencyProvider->provider, $definition);
        $dependencyReference = new DependencyReference($providerHash, $this, get_class($dependencyProvider));
        $this->dependencyContainer[$instanceHash] = $dependencyReference;
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
        $hash = $this->getObjectIndex($instance);
        $type = is_object($instance) ? get_class($instance) : gettype($instance);
        return new DependencyReference($hash, $this, $type);
    }

    /**
     * @param string $log
     */
    private function errorLog($log)
    {
        // error_log($log);
        $this->log .= $log . PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    public function setSingletonInstance($key, $instance)
    {
        $this->singletonContainer[$key] = $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getSingletonInstance($key)
    {
        $instance = isset($this->singletonContainer[$key]) ? $this->singletonContainer[$key] : null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getSingletonKey(BoundDefinition $definition)
    {
        $key = "{$definition->class}-{$definition->interface}-{$definition->name}";

        return $key;
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
        list(
            $this->classMap,
            $this->dependencyContainer
        ) = unserialize($serialized);
    }
}
