<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Aura\Di\ContainerInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Ray\Aop\BindInterface;
use Ray\Aop\Compiler;
use Ray\Aop\CompilerInterface;
use Ray\Aop\Matcher;
use ReflectionClass;
use Serializable;
use SplObjectStorage;

class Injector implements InjectorInterface, \Serializable
{
    /**
     * Container
     *
     * @var ContainerInterface
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
     * @var CompilerInterface
     */
    private $compiler;

    /**
     * @var BoundInstanceInterface
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
     * @Ray\Di\Di\Inject
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
    public static function create(array $modules = [], Cache $cache = null, $tmdDir = null)
    {
        $locator = new Locator;
        $cache = $cache ?: new ArrayCache;
        $locator->setCache($cache);
        $tmdDir = $tmdDir ?: sys_get_temp_dir();
        $annotationReader = $locator->getAnnotationReader();
        Matcher::setAnnotationReader($annotationReader);
        $injector = (new InjectorFactory)->newInstance($modules, $cache, $tmdDir);

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
        // module activation
        $this->module->activate($this);

        if ($this->boundInstance->hasBound($class, $this->module)) {
            return $this->boundInstance->getBound();
        }

        // get bound config
        $definition = $this->boundInstance->getDefinition();

        // be all parameters ready
        $params = $this->boundInstance->bindConstruct($class, $definition->params, $this->module);

        $refClass = new \ReflectionClass($definition->class);

        if ($refClass->isInterface()) {
            return $this->getInstance($definition->class);
        }

        // weave aspect
        $module = $this->module;
        $bind = $module($definition->class, new $this->bind);
        /* @var $bind \Ray\Aop\Bind */
        $hasBinding = $bind->hasBinding();
        $instance = $hasBinding ? $this->compiler->noBindNewInstance($definition->class, $params, $bind) : $refClass->newInstanceArgs($params);

        // do not call constructor twice. ever.
        unset($definition->setter['__construct']);

        // call setter methods
        foreach ($definition->setter as $method => $value) {
            call_user_func_array([$instance, $method], $value);
        }

        // attach interceptors
        if ($hasBinding) {
            $instance->rayAopBind = $bind;
        }

        // logger inject info
        if ($this->logger) {
            $this->logger->log($definition, $params, $definition->setter, $instance, $bind);
        }

        // object life cycle, store singleton instance.
        $this->postInject($instance, $definition);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function enableBindCache()
    {
        AbstractModule::enableInvokeCache();

        return $this;
    }

    /**
     * Post inject procedure
     *
     * @param object          $object
     * @param BoundDefinition $definition
     */
    private function postInject($object, BoundDefinition $definition)
    {
        // set life cycle
        if ($definition) {
            $this->setLifeCycle($object, $definition);
        }

        // set singleton object
        if ($definition->isSingleton) {
            $this->container->set($definition->interface, $object);
        }
    }

    /**
     * Set object life cycle
     *
     * @param object          $instance
     * @param BoundDefinition $definition
     */
    private function setLifeCycle($instance, BoundDefinition $definition)
    {
        $postConstructMethod = $definition->postConstruct;
        if ($postConstructMethod) {
            call_user_func(array($instance, $postConstructMethod));
        }
        if (!is_null($definition->preDestroy)) {
            $this->preDestroyObjects->attach($instance, $definition->preDestroy);
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
