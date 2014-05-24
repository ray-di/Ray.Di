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
use Ray\Aop\Bind;
use Ray\Aop\BindInterface;
use Ray\Aop\Compiler;
use Ray\Aop\CompilerInterface;
use Ray\Di\Exception;
use ReflectionClass;
use ReflectionMethod;
use SplObjectStorage;
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
     * @var BindInterface
     */
    protected $bind;

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
     * @var BoundInstance
     */
    public $boundInstance;

    /**
     * @param ContainerInterface     $container
     * @param AbstractModule         $module
     * @param BindInterface          $bind
     * @param CompilerInterface      $compiler
     * @param LoggerInterface        $logger
     * @param BoundInstanceInterface $boundInstance
     *
     * @Inject
     */
    public function __construct(
        ContainerInterface $container,
        AbstractModule $module,
        BindInterface $bind,
        CompilerInterface $compiler,
        LoggerInterface $logger = null,
        BoundInstanceInterface $boundInstance = null
    ) {
        $this->container = $container;
        $this->module = $module;
        $this->bind = $bind;
        $this->compiler = $compiler;
        $this->logger = $logger;

        $this->preDestroyObjects = new SplObjectStorage;
        $this->config = $container->getForge()->getConfig();
        $this->boundInstance = $boundInstance ?: new BoundInstance($this, $this->config, $container, $logger);
        $this->module->activate($this);
        AnnotationRegistry::registerFile(__DIR__ . '/DiAnnotation.php');
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
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Return aop generated file path
     *
     * @return string
     */
    public function getAopClassDir()
    {
        return $this->compiler->getAopClassDir();
    }

    public function __clone()
    {
        $this->container = clone $this->container;
    }

    /**
     * @param AbstractModule $module
     *
     * @return self
     */
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

        if ($this->boundInstance->hasBound($class, $this->module)) {
            return $this->boundInstance->getBound();
        }

        // get bound config
        list($class, $isSingleton, $interfaceClass, $params, $setter, $definition) = $this->boundInstance->getDefinition();

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
     * @param array $setterDefinitions
     *
     * @return array
     */
    public function getSetter(array $setterDefinitions)
    {
        $injected = [];
        foreach ($setterDefinitions as $setterDefinition) {
            try {
                $injected[] = $this->bindMethod($setterDefinition);
            } catch (Exception\OptionalInjectionNotBound $e) {
                // no optional dependency
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
     * @param array  $param
     * @param string $key
     *
     * @return array
     */
    private function extractParam(array $param, $key)
    {
        $annotate = $param[Definition::PARAM_ANNOTATE];
        $typeHint = $param[Definition::PARAM_TYPEHINT];
        $hasTypeHint =  isset($this->module[$typeHint][$annotate]) &&  isset($this->module[$typeHint][$annotate]) && ($this->module[$typeHint][$annotate] !== []);
        $binding = $hasTypeHint ? $this->module[$typeHint][$annotate] : false;
        $hasNoBound = $binding === false || isset($binding[AbstractModule::TO]) === false;
        if ($hasNoBound) {
            return $this->getNoBoundParam($param, $key);
        }

        return $this->getParam($param, $binding);
    }

    /**
     * @param array  $param
     * @param string $key
     *
     * @return array
     */
    private function getNoBoundParam(array $param, $key)
    {
        if (array_key_exists(Definition::DEFAULT_VAL, $param)) {

            return $param[Definition::DEFAULT_VAL];
        }
        $binding = $this->jitBinding($param, $param[Definition::PARAM_TYPEHINT], $param[Definition::PARAM_ANNOTATE], $key);
        $param = $this->getParam($param, $binding);

        return $param;
    }

    /**
     * @param array $param
     * @param array $binding
     *
     * @return array
     */
    private function getParam(array $param, array $binding)
    {
        list($bindingToType, $target) = $binding[AbstractModule::TO];

        list($param, $bound) = $this->instanceBound($param, $bindingToType, $target, $binding);
        if ($bound) {
            return $param;
        }
        $param = $this->extractNotBoundParam($param[Definition::PARAM_TYPEHINT], $bindingToType, $target);

        return $param;
    }

    /**
     * Return param when not bound
     *
     * @param string $typeHint
     * @param string $bindingToType
     * @param string $target
     *
     * @return array
     */
    private function extractNotBoundParam($typeHint, $bindingToType, $target)
    {
        if ($typeHint === '') {
            $param = $this->getInstanceWithContainer(Scope::PROTOTYPE, $bindingToType, $target);

            return $param;
        }
        $param = $this->typeBound($typeHint, $bindingToType, $target);

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
     * @return array
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
     * @return array
     * @throws Exception\NotBound
     */
    private function constructParams(array $params, $index, \ReflectionParameter $parameter, AbstractModule $module, $class)
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
            $params[$index] = $this->getInstance($classRef->name);

            return $params;
        }
        $msg = is_null($classRef) ? "Valid interface is not found. (array ?)" : "Interface [{$classRef->name}] is not bound.";
        $msg .= " Injection requested at argument #{$index} \${$parameter->name} in {$class} constructor.";
        throw new Exception\NotBound($msg);
    }

    /**
     * @param array  $setter
     * @param object $object
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
        return (string) ($this->module);
    }

    /**
     * Set param by type bound
     *
     * @param string $typeHint
     * @param string $bindingToType
     * @param string $target
     *
     * @return mixed
     */
    private function typeBound($typeHint, $bindingToType, $target)
    {
        list(, , $definition) = $this->config->fetch($typeHint);
        $in = isset($definition[Definition::SCOPE]) ? $definition[Definition::SCOPE] : Scope::PROTOTYPE;
        $param = $this->getInstanceWithContainer($in, $bindingToType, $target);

        return $param;
    }
    /**
     * Set param by instance bound(TO_INSTANCE, TO_CALLABLE, or already set in container)
     *
     * @param array  $param
     * @param string $bindingToType
     * @param mixed  $target
     * @param mixed  $binding
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
     * @param string $in            (Scope::SINGLETON | Scope::PROTOTYPE)
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
        $instance = $isToClassBinding ? $this->getInstance($target) : $this->getProvidedInstance($target);

        if ($in === Scope::SINGLETON) {
            $this->container->set($target, $instance);
        }

        return $instance;
    }

    /**
     * @param string $target interface name
     *
     * @return Compiler
     */
    private function getProvidedInstance($target)
    {
        $provider = $this->getInstance($target);
        /** @var $provider ProviderInterface */
        $instance = $provider->get();
        if ($this->logger) {
            $dependencyProvider = new DependencyProvider($provider, $instance);
            $this->logger->log($target, [], [], $dependencyProvider, new Bind);
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
            throw $this->getNotBoundException($param, $key, $typeHint, $annotate);
        }
        if ($typeHintBy[0] === Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY) {
            return [AbstractModule::TO => [AbstractModule::TO_CLASS, $typeHintBy[1]]];
        }

        return [AbstractModule::TO => [AbstractModule::TO_PROVIDER, $typeHintBy[1]]];
    }

    /**
     * @param array  $param
     * @param string $key
     * @param string $typeHint
     * @param string $annotate
     *
     * @return Exception\NotBound
     * @throws Exception\OptionalInjectionNotBound
     */
    private function getNotBoundException(array $param, $key, $typeHint, $annotate)
    {
        if ($param[Definition::OPTIONAL] === true) {
            throw new Exception\OptionalInjectionNotBound($key);
        }
        $name = $param[Definition::PARAM_NAME];
        $class = array_pop($this->classes);
        $msg = "typehint='{$typeHint}', annotate='{$annotate}' for \${$name} in class '{$class}'";
        $e = (new Exception\NotBound($msg))->setModule($this->module);

        return $e;
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
                $this->config,
                $this->boundInstance
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
            $this->config,
            $this->boundInstance
        ) = unserialize($data);

        AnnotationRegistry::registerFile(__DIR__ . '/DiAnnotation.php');
        register_shutdown_function(function () {
            // @codeCoverageIgnoreStart
            $this->notifyPreShutdown();
            // @codeCoverageIgnoreEnd
        });

    }
}
