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
use SplObjectStorage;
use PHPParser_PrettyPrinter_Default;
use Serializable;
use Ray\Di\Di\Inject;

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
     * @var BoundInstance
     */
    public $boundInstance;

    private $extractor;

    private $classs;

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
        $this->boundInstance = $boundInstance ?: new BoundInstance($this, $this->config, $container, $module, $logger);
        $this->extractor = new Binder($module, $this, $this->config, $logger);
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
        $this->class = $class;

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
    public function constructorInject($class, array $params, AbstractModule $module)
    {
        $ref = method_exists($class, '__construct') ? new \ReflectionMethod($class, '__construct') : false;
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
        if ($this->hasConstructorBinding($module, $class)) {
            $params[$index] = $module[$class]['*'][AbstractModule::TO][1][$parameter->name];

            return $params;
        }
        $param = $this->getNoBoundConstructorParam($parameter, $params, $index, $class);

        return $param;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param array                $params
     * @param string               $index
     * @param string               $class
     *
     * @return array
     * @throws Exception\NotBound
     */
    private function getNoBoundConstructorParam(\ReflectionParameter $parameter, array $params, $index, $class)
    {
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
     * @param AbstractModule $module
     * @param string         $class
     *
     * @return bool
     */
    private function hasConstructorBinding(AbstractModule $module, $class)
    {
        return ($module[$class]['*'][AbstractModule::TO][0] === AbstractModule::TO_CONSTRUCTOR);
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
