<?php

namespace Ray\Di;

use Aura\Di\ConfigInterface;
use Aura\Di\ContainerInterface;
use Aura\Di\Lazy;
use Ray\Di\Definition;

class BoundInstance implements BoundInstanceInterface
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var AbstractModule
     */
    private $module;

    /**
     * @var \Aura\Di\ContainerInterface
     */
    private $container;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var mixed
     */
    private $bound;

    /**
     * @var array
     */
    private $definition;

    /**
     * @var Binder
     */
    private $binder;

    /**
     * @var string
     */
    private $class;

    /**
     * @param InjectorInterface  $injector
     * @param ContainerInterface $container
     * @param AbstractModule     $module
     * @param LoggerInterface    $logger
     * @param Binder             $binder
     */
    public function __construct(
        InjectorInterface $injector,
        ContainerInterface $container,
        AbstractModule $module,
        LoggerInterface $logger = null,
        Binder $binder = null
    ) {
        $this->injector = $injector;
        $this->config = $container->getForge()->getConfig();
        $this->container = $container;
        $this->logger = $logger;
        $this->binder = $binder ?: new Binder($module, $injector, $this->config, $logger);
    }

    /**
     * @param string         $class
     * @param AbstractModule $module
     *
     * @return bool
     */
    public function hasBound($class, AbstractModule $module)
    {
        $this->class = $class;
        return $this->binding($class, $module);
    }

    /**
     * @return object
     */
    public function getBound()
    {
        return $this->bound;
    }

    /**
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Return bound object or inject info
     *
     * @param string $class
     *
     * @return boolean
     */
    private function binding($class, AbstractModule $module)
    {
        $this->module = $module;
        $class = $this->removeLeadingBackSlash($class);
        $isAbstract = $this->isAbstract($class);
        list(, , $definition) = $this->config->fetch($class);
        $isSingleton = false;
        $interfaceClass = '';
        if ($isAbstract) {
            return $this->abstractBinding($class, $definition);
        }
        $bound = $this->getBoundClass($this->module->bindings, $definition, $class);
        if (is_object($bound)) {
            $this->bound =  $bound;
            $this->definition = [];

            return true;
        }
        $this->bound = null;
        $this->definition = $this->getBoundDefinition($class, $isSingleton, $interfaceClass);

        return false;
    }

    /**
     * @param string       $class
     * @param \ArrayObject $definition
     *
     * @return boolean
     * @throws Exception\NotBound
     */
    private function abstractBinding($class, $definition)
    {
        $bound = $this->getBoundClass($this->module->bindings, $definition, $class);
        if ($bound === false) {
            throw new Exception\NotBound($class);
        }
        if (is_object($bound)) {
            $this->bound = $bound;
            $this->definition = [];

            return true;
        }
        list($class, $isSingleton, $interfaceClass) = $bound;
        $this->bound = [];
        $this->definition =  $this->getBoundDefinition($class, $isSingleton, $interfaceClass);

        return false;
    }

    /**
     * @param string $class
     * @param bool   $isSingleton
     * @param string $interfaceClass
     *
     * @return array
     */
    private function getBoundDefinition($class, $isSingleton, $interfaceClass)
    {
        list($config, $setter, $definition) = $this->config->fetch($class);
        $isSingleton = $isSingleton || strcasecmp(Scope::SINGLETON, $definition[Definition::SCOPE]) === 0;
        $hasDirectBinding = isset($this->module->bindings[$class]);
        /** @var $definition Definition */
        if ($definition->hasDefinition() || $hasDirectBinding) {
            list($config, $setter) = $this->bindModule($setter, $definition);
        }

        return [$class, $isSingleton, $interfaceClass, $config, $setter, $definition];

    }

    /**
     * return isAbstract ?
     *
     * @param string $class
     *
     * @return bool
     * @throws Exception\NotReadable
     */
    private function isAbstract($class)
    {
        try {
            $refClass = new \ReflectionClass($class);
            $isAbstract = $refClass->isInterface() || $refClass->isAbstract();
        } catch (\ReflectionException $e) {
            throw new Exception\NotReadable($class);
        }

        return $isAbstract;
    }

    /**
     * Remove leading back slash
     *
     * @param string $class
     *
     * @return string
     */
    private function removeLeadingBackSlash($class)
    {
        $isLeadingBackSlash = (strlen($class) > 0 && $class[0] === '\\');
        if ($isLeadingBackSlash === true) {
            $class = substr($class, 1);
        }

        return $class;
    }

    /**
     * Get bound class or object
     *
     * @param \ArrayObject  $bindings
     * @param mixed         $definition
     * @param string        $class
     *
     * @return array|object
     * @throws Exception\NotBound
     */
    private function getBoundClass($bindings, $definition, $class)
    {
        if ($this->isBound($bindings, $class)) {
            return false;
        }

        $toType = $bindings[$class]['*']['to'][0];

        if ($toType === AbstractModule::TO_PROVIDER) {
            $instance = $this->getToProviderBound($bindings, $class);

            return $instance;
        }

        return $this->getBoundClassByInfo($class, $definition, $bindings, $toType);
    }

    /**
     * @param string       $class
     * @param \ArrayObject $definition
     * @param \ArrayObject $bindings
     * @param string       $toType
     *
     * @return array|object
     */
    private function getBoundClassByInfo($class, $definition, $bindings, $toType)
    {
        list($isSingleton, $interfaceClass) = $this->getBindingInfo($class, $definition, $bindings);

        if ($isSingleton && $this->container->has($interfaceClass)) {
            $object = $this->container->get($interfaceClass);

            return $object;
        }

        if ($toType === AbstractModule::TO_INSTANCE) {
            return $bindings[$class]['*']['to'][1];
        }

        if ($toType === AbstractModule::TO_CLASS) {
            $class = $bindings[$class]['*']['to'][1];
        }

        $boundInfo = [$class, $isSingleton, $interfaceClass];

        return $boundInfo;
    }

    /**
     * Return $isSingleton, $interfaceClass
     *
     * @param string       $class
     * @param \ArrayObject $definition
     * @param \ArrayObject $bindings
     *
     * @return array [$isSingleton, $interfaceClass]
     */
    private function getBindingInfo($class, $definition, $bindings)
    {
        $inType = isset($bindings[$class]['*'][AbstractModule::IN]) ? $bindings[$class]['*'][AbstractModule::IN] : null;
        $inType = is_array($inType) ? $inType[0] : $inType;
        $isSingleton = $inType === Scope::SINGLETON || $definition['Scope'] == Scope::SINGLETON;
        $interfaceClass = $class;

        return [$isSingleton, $interfaceClass];

    }

    /**
     * Throw exception if not bound
     *
     * @param \ArrayObject $bindings
     * @param string       $class
     *
     * @return bool
     */
    private function isBound($bindings, $class)
    {
        return (!isset($bindings[$class]) || !isset($bindings[$class]['*']['to'][0]));
    }

    /**
     * @param \ArrayObject $bindings
     * @param string       $class
     *
     * @return object
     */
    private function getToProviderBound(\ArrayObject $bindings, $class)
    {
        $provider = $bindings[$class]['*']['to'][1];
        $in = isset($bindings[$class]['*']['in']) ? $bindings[$class]['*']['in'] : null;
        if ($in !== Scope::SINGLETON) {
            $instance = $this->injector->getInstance($provider)->get();

            return $instance;
        }
        if ($this->container->has($class)) {
            return $this->container->get($class);
        }
        $instance = $this->injector->getInstance($provider)->get();
        $this->container->set($class, $instance);

        return $instance;

    }

    /**
     * Return dependency using modules.
     *
     * @param array      $setter
     * @param Definition $definition
     *
     * @return array             <$constructorParams, $setter>
     * @throws Exception\Binding
     * @throws \LogicException
     */
    private function bindModule(array $setter, Definition $definition)
    {
        // main
        $setterDefinitions = (isset($definition[Definition::INJECT][Definition::INJECT_SETTER])) ? $definition[Definition::INJECT][Definition::INJECT_SETTER] : null;
        if ($setterDefinitions) {
            $setter = $this->getSetter($setterDefinitions);
        }
        // constructor injection ?
        $params = isset($setter['__construct']) ? $setter['__construct'] : [];
        $result = [$params, $setter];

        return $result;
    }

    /**
     * @param array $setterDefinitions
     *
     * @return array
     */
    private function getSetter(array $setterDefinitions)
    {
        $bound = [];
        foreach ($setterDefinitions as $setterDefinition) {
            try {
                $bound[] = $this->binder->bindMethod($this->module, $this->class, $setterDefinition);
            } catch (Exception\OptionalInjectionNotBound $e) {
                // no optional dependency
            }
        }
        $setter = [];
        foreach ($bound as $item) {
            list($setterMethod, $object) = $item;
            $setter[$setterMethod] = $object;
        }

        return $setter;
    }

    /**
     * @param string         $class
     * @param array          $params
     * @param AbstractModule $module
     *
     * @return array
     */
    public function bindConstruct($class, array $params, AbstractModule $module)
    {
        $params = $this->instantiateParams($params);
        return $this->binder->bindConstructor($class, $params, $module);
    }

    /**
     * Return parameters
     *
     * @param array $params
     *
     * @return array
     */
    private function instantiateParams(array $params)
    {
        // lazy-load params as needed
        $keys = array_keys($params);
        foreach ($keys as $key) {
            if ($params[$key] instanceof \Aura\Di\Lazy) {
                $params[$key] = $params[$key]();
            }
        }

        return $params;
    }
}
