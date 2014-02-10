<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Aura\Di\ContainerInterface;
use Aura\Di\Lazy;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\Cache;
use LogicException;
use Ray\Aop\Bind;
use Ray\Aop\BindInterface;
use Ray\Aop\Compiler;
use Ray\Aop\CompilerInterface;
use Ray\Di\Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use SplObjectStorage;
use ArrayObject;
use PHPParser_PrettyPrinter_Default;
use Serializable;
use Ray\Di\Di\Inject;

/**
 * Dependency Injector
 */
class Injector implements InjectorInterface, \Serializable
{
    /**
     * Config
     *
     * @var Config
     */
    protected $config;

    /**
     * Container
     *
     * @var \Ray\Di\Container
     */
    protected $container;

    /**
     * Binding module
     *
     * @var AbstractModule
     */
    protected $module;

    /**
     * Pre-destroy objects
     *
     * @var SplObjectStorage
     */
    private $preDestroyObjects;

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Compiler(Aspect Weaver)
     *
     * @var Compiler
     */
    private $compiler;

    /**
     * Target classes
     *
     * @var array
     */
    private $classes = [];

    /**
     * @param ContainerInterface $container
     * @param AbstractModule     $module
     * @param BindInterface      $bind
     * @param CompilerInterface  $compiler
     * @param LoggerInterface    $logger
     *
     * @Inject
     */
    public function __construct(
        ContainerInterface $container,
        AbstractModule $module,
        BindInterface $bind,
        CompilerInterface $compiler,
        LoggerInterface $logger = null
    ) {
        $this->container = $container;
        $this->module = $module;
        $this->bind = $bind;
        $this->compiler = $compiler;
        $this->logger = $logger;

        $this->preDestroyObjects = new SplObjectStorage;
        $this->config = $container->getForge()->getConfig();
        $this->module->activate($this);

        AnnotationRegistry::registerAutoloadNamespace('Ray\Di\Di', dirname(dirname(__DIR__)));
    }

    public function __destruct()
    {
        $this->notifyPreShutdown();
    }

    /**
     * {@inheritdoc}
     */
    public static function create(array $modules = [], Cache $cache = null)
    {
        $annotationReader = ($cache instanceof Cache) ? new CachedReader(new AnnotationReader, $cache) : new AnnotationReader;
        $injector = new self(
            new Container(new Forge(new Config(new Annotation(new Definition, $annotationReader)))),
            new EmptyModule,
            new Bind,
            new Compiler(
                sys_get_temp_dir(),
                new PHPParser_PrettyPrinter_Default
            ),
            new Logger
        );

        if (count($modules) > 0) {
            $module = array_shift($modules);
            foreach ($modules as $extraModule) {
                /* @var $module AbstractModule */
                $module->install($extraModule);
            }
            $injector->setModule($module);
        }

        return $injector;
    }

    /**
     * {@inheritdoc}
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * {@inheritdoc}
     */
    public function setModule(AbstractModule $module)
    {
        $module->activate($this);
        $this->module = $module;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Return aop generated file path
     *
     * @return string
     */
    public function getAopClassDir()
    {
        return $this->compiler->classDir;
    }

    public function __clone()
    {
        $this->container = clone $this->container;
    }

    public function __invoke(AbstractModule $module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($class)
    {
        // log
        $this->classes[] = $class;

        $bound = $this->getBound($class);

        // return singleton bound object if exists
        if (is_object($bound)) {
            return $bound;
        }

        // get bound config
        list($class, $isSingleton, $interfaceClass, $params, $setter, $definition) = $bound;

        // instantiate parameters
        $params = $this->instantiateParams($params);

        // be all parameters ready
        $params = $this->constructorInject($class, $params, $this->module);

        $refClass = new \ReflectionClass($class);

        if ($refClass->isInterface()) {
            return $this->getInstance($class);
        }

        // weave aspect
        $module = $this->module;
        $bind = $module($class, new $this->bind);
        /* @var $bind \Ray\Aop\Bind */

        $object = $bind->hasBinding() ?
            $this->compiler->newInstance($class, $params, $bind) : $this->newInstance($class, $params) ;

        // do not call constructor twice. ever.
        unset($setter['__construct']);

        // call setter methods
        $this->setterMethod($setter, $object);

        // logger inject info
        if ($this->logger) {
            $this->logger->log($class, $params, $setter, $object, $bind);
        }

        // Object life cycle, Singleton, and Save cache
        $this->postInject($object, $definition, $isSingleton, $interfaceClass);

        return $object;
    }

    /**
     * Notify pre-destroy
     *
     * @return void
     */
    private function notifyPreShutdown()
    {
        $this->preDestroyObjects->rewind();
        while ($this->preDestroyObjects->valid()) {
            $object = $this->preDestroyObjects->current();
            $method = $this->preDestroyObjects->getInfo();
            $object->$method();
            $this->preDestroyObjects->next();
        }
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
            if ($params[$key] instanceof Lazy) {
                $params[$key] = $params[$key]();
            }
        }

        return $params;
    }

    /**
     * Post inject procedure
     *
     * @param object     $object
     * @param Definition $definition
     * @param bool       $isSingleton
     * @param string     $interfaceClass
     */
    private function postInject($object, Definition $definition, $isSingleton, $interfaceClass)
    {
        // set life cycle
        if ($definition) {
            $this->setLifeCycle($object, $definition);
        }

        // set singleton object
        if ($isSingleton) {
            $this->container->set($interfaceClass, $object);
        }
    }

    /**
     * Return new instance
     *
     * @param string $class
     * @param array  $params
     *
     * @return object
     */
    private function newInstance($class, array $params)
    {
        return call_user_func_array(
            [$this->config->getReflect($class), 'newInstance'],
            $params
        );
    }

    /**
     * Return bound object or inject info
     *
     * @param string $class
     *
     * @return array|object
     * @throws Exception\NotReadable
     */
    private function getBound($class)
    {
        $class = $this->removeLeadingBackSlash($class);
        $isAbstract = $this->isAbstract($class);
        list($config, $setter, $definition) = $this->config->fetch($class);
        $interfaceClass = $isSingleton = false;
        if ($isAbstract) {
            $bound = $this->getBoundClass($this->module->bindings, $definition, $class);
            if (is_object($bound)) {
                return $bound;
            }
            list($class, $isSingleton, $interfaceClass) = $bound;
            list($config, $setter, $definition) = $this->config->fetch($class);
        } elseif (! $isAbstract) {
            try {
                $bound = $this->getBoundClass($this->module->bindings, $definition, $class);
                if (is_object($bound)) {
                    return $bound;
                }
            } catch (Exception\NotBound $e) {

            }
        }
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
            $refClass = new ReflectionClass($class);
            $isAbstract = $refClass->isInterface() || $refClass->isAbstract();
        } catch (ReflectionException $e) {
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
     * @param ArrayObject  $bindings   array | \ArrayAccess
     * @param mixed  $definition
     * @param string $class
     *
     * @return array|object
     * @throws Exception\NotBound
     */
    private function getBoundClass($bindings, $definition, $class)
    {
        $this->checkNotBound($bindings, $class);

        $toType = $bindings[$class]['*']['to'][0];

        if ($toType === AbstractModule::TO_PROVIDER) {
            return $this->getToProviderBound($bindings, $class);
        }

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

        return [$class, $isSingleton, $interfaceClass];
    }

    /**
     * Return $isSingleton, $interfaceClass
     *
     * @param string $class
     * @param array  $definition
     * @param ArrayObject  $bindings
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
     * @param ArrayObject  $bindings
     * @param string $class
     *
     * @throws Exception\NotBound
     */
    private function checkNotBound($bindings, $class)
    {
        if (!isset($bindings[$class]) || !isset($bindings[$class]['*']['to'][0])) {
            $msg = "Interface \"$class\" is not bound.";
            throw new Exception\NotBound($msg);
        }
    }

    /**
     * @param ArrayObject $bindings
     * @param string $class
     *
     * @return object
     */
    private function getToProviderBound(ArrayObject $bindings, $class)
    {
        $provider = $bindings[$class]['*']['to'][1];
        $in = isset($bindings[$class]['*']['in']) ? $bindings[$class]['*']['in'] : null;
        if ($in !== Scope::SINGLETON) {
            return $this->getInstance($provider)->get();
        }
        if (!$this->container->has($class)) {
            $object = $this->getInstance($provider)->get();
            $this->container->set($class, $object);

        }

        return $this->container->get($class);

    }
    /**
     * Return dependency using modules.
     *
     * @param array      $setter
     * @param Definition $definition
     *
     * @return array <$constructorParams, $setter>
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
        $injected = [];
        foreach ($setterDefinitions as $setterDefinition) {
            try {
                $injected[] = $this->bindMethod($setterDefinition);
            } catch (Exception\OptionalInjectionNotBound $e) {
            }
        }
        $setter = [];
        foreach ($injected as $item) {
            list($setterMethod, $object) = $item;
            $setter[$setterMethod] = $object;
        }

        return $setter;
    }

    /**
     * Bind method
     *
     * @param array $setterDefinition
     *
     * @return array
     */
    private function bindMethod(array $setterDefinition)
    {
        list($method, $settings) = each($setterDefinition);
        // Set one parameter with definition, or JIT binding.
        foreach ($settings as $key => &$param) {
            $param = $this->extractParam($param, $key);
        }
        return [$method, $settings];
    }

    /**
     * Extract parameter as defined
     *
     * @param array $param
     * @param string $key
     *
     * @return array
     */
    private function extractParam(array $param, $key)
    {
        $annotate = $param[Definition::PARAM_ANNOTATE];
        $typeHint = $param[Definition::PARAM_TYPEHINT];
        $hasTypeHint = isset($this->module[$typeHint]) && isset($this->module[$typeHint][$annotate]) && ($this->module[$typeHint][$annotate] !== []);
        $binding = $hasTypeHint ? $this->module[$typeHint][$annotate] : false;
        $isNotBinding = $binding === false || isset($binding[AbstractModule::TO]) === false;
        if ($isNotBinding && array_key_exists(Definition::DEFAULT_VAL, $param)) {
            // default value
            $param = $param[Definition::DEFAULT_VAL];
            return $param;
        }
        if ($isNotBinding) {
            // default binding by @ImplementedBy or @ProviderBy
            $binding = $this->jitBinding($param, $typeHint, $annotate, $key);
        }
        list($bindingToType, $target) = $binding[AbstractModule::TO];

        list($param, $bound) = $this->instanceBound($param, $bindingToType, $target, $binding);
        if ($bound) {
            return $param;
        }

        return $this->extractNotBoundParam($param, $typeHint, $bindingToType, $target);
    }

    /**
     * Return param when not bound
     *
     * @param array  $param
     * @param string $typeHint
     * @param string $bindingToType
     * @param string $target
     *
     * @return array
     */
    private function extractNotBoundParam(array $param, $typeHint, $bindingToType, $target)
    {
        if ($typeHint === '') {
            $param = $this->getInstanceWithContainer(Scope::PROTOTYPE, $bindingToType, $target);
            return $param;
        }
        $param = $this->typeBound($param, $typeHint, $bindingToType, $target);

        return $param;

    }

    /**
     * Return parameter using TO_CONSTRUCTOR
     *
     * 1) If parameter is provided, return. (check)
     * 2) If parameter is NOT provided and TO_CONSTRUCTOR binding is available, return parameter with it
     * 3) No binding found, throw exception.
     *
     * @param string         $class
     * @param array          $params
     * @param AbstractModule $module
     *
     * @return void
     * @throws Exception\NotBound
     */
    private function constructorInject($class, array $params, AbstractModule $module)
    {
        $ref = method_exists($class, '__construct') ? new ReflectionMethod($class, '__construct') : false;
        if ($ref === false) {
            return $params;
        }
        $parameters = $ref->getParameters();
        foreach ($parameters as $index => $parameter) {
            /* @var $parameter \ReflectionParameter */
            $params = $this->constructParams($params, $index, $parameter, $module, $class);
        }

        return $params;
    }

    /**
     * @param array                $params
     * @param int                  $index
     * @param \ReflectionParameter $parameter
     * @param AbstractModule       $module
     * @param string               $class
     *
     * @return void
     * @throws Exception\NotBound
     */
    private function constructParams($params, $index, \ReflectionParameter $parameter, AbstractModule $module, $class)
    {
        // has binding ?
        $params = array_values($params);
        if (isset($params[$index])) {
            return $params;
        }
        $hasConstructorBinding = ($module[$class]['*'][AbstractModule::TO][0] === AbstractModule::TO_CONSTRUCTOR);
        if ($hasConstructorBinding) {
            $params[$index] = $module[$class]['*'][AbstractModule::TO][1][$parameter->name];
            return $params;
        }
        // has constructor default value ?
        if ($parameter->isDefaultValueAvailable() === true) {
            return $params;
        }
        // is typehint class ?
        $classRef = $parameter->getClass();
        if ($classRef && !$classRef->isInterface()) {
            $params[$index] = $this->getInstance($classRef->getName());
            return $params;
        }
        $msg = is_null($classRef) ? "Valid interface is not found. (array ?)" : "Interface [{$classRef->name}] is not bound.";
        $msg .= " Injection requested at argument #{$index} \${$parameter->name} in {$class} constructor.";
        throw new Exception\NotBound($msg);
    }

    /**
     * @param array $setter
     * @param       $object
     */
    private function setterMethod(array $setter, $object)
    {
        foreach ($setter as $method => $value) {
            call_user_func_array([$object, $method], $value);
        }
    }

    /**
     * Set object life cycle
     *
     * @param object     $instance
     * @param Definition $definition
     *
     * @return void
     */
    private function setLifeCycle($instance, Definition $definition = null)
    {
        $postConstructMethod = $definition[Definition::POST_CONSTRUCT];
        if ($postConstructMethod) {
            call_user_func(array($instance, $postConstructMethod));
        }
        if (!is_null($definition[Definition::PRE_DESTROY])) {
            $this->preDestroyObjects->attach($instance, $definition[Definition::PRE_DESTROY]);
        }

    }

    /**
     * Return module information as string
     *
     * @return string
     */
    public function __toString()
    {
        return (string)($this->module);
    }

    /**
     * Set param by type bound
     *
     * @param mixed  $param
     * @param string $typeHint
     * @param string $bindingToType
     * @param string  $target
     *
     * @return mixed
     */
    private function typeBound($param, $typeHint, $bindingToType, $target)
    {
        list($param, , $definition) = $this->config->fetch($typeHint);
        $in = isset($definition[Definition::SCOPE]) ? $definition[Definition::SCOPE] : Scope::PROTOTYPE;
        $param = $this->getInstanceWithContainer($in, $bindingToType, $target);

        return $param;
    }
    /**
     * Set param by instance bound(TO_INSTANCE, TO_CALLABLE, or already set in container)
     *
     * @param $param
     * @param $bindingToType
     * @param $target
     * @param $binding
     *
     * @return array [$param, $isBound]
     */
    private function instanceBound($param, $bindingToType, $target, $binding)
    {
        if ($bindingToType === AbstractModule::TO_INSTANCE) {
            return [$target, true];
        }

        if ($bindingToType === AbstractModule::TO_CALLABLE) {
            /* @var $target \Closure */
            return [$target(), true];
        }

        if (isset($binding[AbstractModule::IN])) {
            $param = $this->getInstanceWithContainer($binding[AbstractModule::IN], $bindingToType, $target);
            return [$param, true];
        }

        return [$param, false];

    }

    /**
     * Get instance with container
     *
     * @param string $in (Scope::SINGLETON | Scope::PROTOTYPE)
     * @param string $bindingToType
     * @param mixed  $target
     *
     * @return mixed
     */
    private function getInstanceWithContainer($in, $bindingToType, $target)
    {
        if ($in === Scope::SINGLETON && $this->container->has($target)) {
            $instance = $this->container->get($target);

            return $instance;
        }
        $isToClassBinding = ($bindingToType === AbstractModule::TO_CLASS);
        $instance = $isToClassBinding ? $this->getInstance($target) : $this->getInstance($target)->get();

        if ($in === Scope::SINGLETON) {
            $this->container->set($target, $instance);
        }

        return $instance;
    }

    /**
     * JIT binding
     *
     * @param array  $param
     * @param string $typeHint
     * @param string $annotate
     * @param string $key
     *
     * @return array
     * @throws Exception\OptionalInjectionNotBound
     * @throws Exception\NotBound
     */
    private function jitBinding(array $param, $typeHint, $annotate, $key)
    {
        $typeHintBy = $param[Definition::PARAM_TYPEHINT_BY];
        if ($typeHintBy == []) {
            $this->raiseNotBoundException($param, $key, $typeHint, $annotate);
        }
        if ($typeHintBy[0] === Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY) {
            return [AbstractModule::TO => [AbstractModule::TO_CLASS, $typeHintBy[1]]];
        }

        return [AbstractModule::TO => [AbstractModule::TO_PROVIDER, $typeHintBy[1]]];
    }

    /**
     * @param $param
     * @param string $key
     * @param string $typeHint
     * @param string $annotate
     *
     * @throws Exception\OptionalInjectionNotBound
     * @throws Exception\NotBound
     */
    private function raiseNotBoundException($param, $key, $typeHint, $annotate)
    {
        if ($param[Definition::OPTIONAL] === true) {
            throw new Exception\OptionalInjectionNotBound($key);
        }
        $name = $param[Definition::PARAM_NAME];
        $class = array_pop($this->classes);
        $msg = "typehint='{$typeHint}', annotate='{$annotate}' for \${$name} in class '{$class}'";
        $e = (new Exception\NotBound($msg))->setModule($this->module);
        throw $e;
    }

    public function serialize()
    {
        $data = serialize(
            [
                $this->container,
                $this->module,
                $this->bind,
                $this->compiler,
                $this->logger,
                $this->preDestroyObjects,
                $this->config
            ]
        );

        return $data;
    }

    public function unserialize($data)
    {
        list(
            $this->container,
            $this->module,
            $this->bind,
            $this->compiler,
            $this->logger,
            $this->preDestroyObjects,
            $this->config
        ) = unserialize($data);

        AnnotationRegistry::registerAutoloadNamespace('Ray\Di\Di', dirname(dirname(__DIR__)));
        register_shutdown_function(function () {
            // @codeCoverageIgnoreStart
            $this->notifyPreShutdown();
            // @codeCoverageIgnoreEnd
        });
    }
}
