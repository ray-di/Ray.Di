<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Aura\Di\ContainerInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\Cache;
use Ray\Aop\BindInterface;
use Ray\Aop\Compiler;
use Ray\Aop\CompilerInterface;
use Ray\Aop\Matcher;
use ReflectionClass;
use SplObjectStorage;
use Serializable;
use Ray\Di\Di\Inject;

class Injector implements InjectorInterface, \Serializable
{
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
        $this->boundInstance = $boundInstance ?: new BoundInstance($this, $container, $module, $logger);
        $this->module->activate($this);
        AnnotationRegistry::registerFile(__DIR__ . '/DiAnnotation.php');
    }

    /**
     * Notify pre-destroy
     */
    public function __destruct()
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
     * {@inheritdoc}
     */
    public static function create(array $modules = [], Cache $cache = null)
    {
        $annotationReaderFactory = new AnnotationReaderFactory;
        if (! is_null($cache)) {
            $annotationReaderFactory->setCache($cache);
        }
        $annotationReader = $annotationReaderFactory->getInstance();
        Matcher::setAnnotationReader($annotationReader);

        return (new InjectorFactory)->newInstance($modules);
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
        if ($this->boundInstance->hasBound($class, $this->module)) {
            return $this->boundInstance->getBound();
        }

        // get bound config
        list($class, $isSingleton, $interfaceClass, $params, $setter, $definition) = $this->boundInstance->getDefinition();

        // be all parameters ready
        $params = $this->boundInstance->bindConstruct($class, $params, $this->module);

        $refClass = new \ReflectionClass($class);

        if ($refClass->isInterface()) {
            return $this->getInstance($class);
        }

        // weave aspect
        $module = $this->module;
        $bind = $module($class, new $this->bind);
        /* @var $bind \Ray\Aop\Bind */

        $object = $bind->hasBinding() ?
            $this->compiler->newInstance($class, $params, $bind) : (new \ReflectionClass($class))->newInstanceArgs($params);

        // do not call constructor twice. ever.
        unset($setter['__construct']);

        // call setter methods
        foreach ($setter as $method => $value) {
            call_user_func_array([$object, $method], $value);
        }

        // logger inject info
        if ($this->logger) {
            $this->logger->log($class, $params, $setter, $object, $bind);
        }

        // Object life cycle, Singleton, and Save cache
        $this->postInject($object, $definition, $isSingleton, $interfaceClass);

        return $object;
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
            $this->boundInstance
        ) = unserialize($data);

        AnnotationRegistry::registerFile(__DIR__ . '/DiAnnotation.php');
        register_shutdown_function(function () {
            // @codeCoverageIgnoreStart
            $this->__destruct();
            // @codeCoverageIgnoreEnd
        });

    }
}
