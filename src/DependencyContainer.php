<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind;

final class DependencyContainer
{
    private $refId = 0;
    private $objectStorage;

    /**
     * @var DependencyFactory[]
     */
    private $container = [];
    private $logger;

    public function __construct(CompilationLoggerInterface $logger)
    {
        $this->objectStorage = new \SplObjectStorage;
        $this->logger = $logger;
    }

    public function attach($object)
    {
        if ($this->objectStorage->contains($object)) {
            return $this->objectStorage[$object];
        }
        $this->refId++;
        $this->objectStorage[$object] = (string) $this->refId;
        $this->container[$this->refId] = $object;
        $log = sprintf(
            'ray/di.install ref:%s class:%s',
            $this->refId,
            get_class($object)
        );
        error_log($log);

        return $this->refId;
    }

    public function attachProvider(DependencyProvider $dependencyProvider)
    {
        $this->attach($dependencyProvider->instance);
        $providerRefId = $this->attach($dependencyProvider->provider);
        $dependencyReference = new DependencyReference($providerRefId, $this->logger, get_class($dependencyProvider));
        $this->attach($dependencyReference);
    }

    public function attachFactory($instance, $params, $setters, $postConstructMethod)
    {
        // parameters
        $params = $this->makeParamRef($params);
        foreach ($setters as &$methodPrams) {
            $methodPrams = $this->makeParamRef($methodPrams);
        }
        // instantiate
        $dependencyFactory = new DependencyFactory($instance, $params, $setters, $this->logger);
        $dependencyFactory->setPostConstruct($postConstructMethod);
        // aop ?
        if (isset($instance->rayAopBind)) {
            $interceptors = $this->buildInterceptor($instance);
            $dependencyFactory->setInterceptors($interceptors);
            var_dump($dependencyFactory);
        }
        $refId = $this->attach($dependencyFactory);
        $dependencyFactory->setRefId($refId);

        $diLog = $dependencyFactory->getDependencyLog();
        if ($diLog) {
            error_log('ray/di.depends ' . $diLog);
        }
        $aopLog = $dependencyFactory->getInterceptorLog();
        if ($aopLog) {
            error_log('ray/di.aspect  ' . $aopLog);
        }

    }

    public function getDependencyReference($instance)
    {
        $refId = $this->attach($instance);
        $type = is_object($instance) ? get_class($instance) : gettype($instance);

        return new DependencyReference($refId, $this->logger, $type);
    }

    public function newInstance($refId)
    {
        $instance = $this->container[$refId];
        return $instance instanceof DependencyFactory ? $instance->get() : $instance;
    }

    public function get($refId)
    {
        return $this->container[$refId];
    }

    public function pop()
    {
        $container = $this->container;

        return array_pop($container);
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
                $param = $this->getDependencyReference($param);
            }
        }

        return $params;
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
                $methodInterceptor = $this->getDependencyReference($methodInterceptor);
            }
        }

        return $boundInterceptors;
    }


}
