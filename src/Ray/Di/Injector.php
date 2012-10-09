<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Exception\OptionalInjectionNotBound;
use Ray\Di\Exception\Binding;
use Ray\Aop\Bind;
use Ray\Aop\Weaver;
use Aura\Di\Lazy;
use Aura\Di\ContainerInterface;
use Aura\Di\Exception\ContainerLocked;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;
use SplObjectStorage;

/**
 * Dependency Injector
 *
 * @package Ray.Di
 */
class Injector implements InjectorInterface
{
    /**
     * Inject annotation with optional=false
     *
     * @var bool
     */
    const OPTIONAL_BINDING_NOT_BOUND = false;

    /**
     * Config
     *
     * @var Config
     */
    protected $config;

    /**
     * Params
     *
     * A convenient reference to the Config::$params object, which itself
     * is contained by the Forge object.
     *
     * @var \ArrayObject
     */
    protected $params;

    /**
     * Setter
     *
     * A convenient reference to the Config::$setter object, which itself
     * is contained by the Forge object.
     *
     * @var \ArrayObject
     */
    protected $setter;

    /**
     * Container
     *
     * @var Container
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
    private $log;

    /**
     * Constructor
     *
     * @param ContainerInterface $container The class to instantiate.
     * @param AbstractModule     $module    Binding configuration module
     */
    public function __construct(ContainerInterface $container, AbstractModule $module = null)
    {
        $this->container = $container;
        $this->config = $container->getForge()->getConfig();
        $this->preDestroyObjects = new SplObjectStorage;
        if ($module == null) {
            $module = new EmptyModule;
        }
        $this->module = $module;
        $this->bind = new Bind;
        $this->params = $this->config->getParams();
        $this->setter = $this->config->getSetter();
    }

    /**
     * Set Logger
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->log = $logger;
    }

    /**
     * Injector builder
     *
     * @param AbstractModule[] $modules
     * @param bool $useApcCache
     *
     * @return Injector
     */
    public static function create(array $modules = [], $useApcCache = true)
    {
        $config = $useApcCache ? __NAMESPACE__ . '\ApcConfig' : __NAMESPACE__ . '\Config';
        $injector = new self(new Container(new Forge(new $config(new Annotation(new Definition, new AnnotationReader)))));
        if (count($modules) > 0) {
            $module = array_shift($modules);
            foreach ($modules as $extraModule) {
                /* @var $module AbstractModule */
                $module->install($extraModule);
            }
            // dirty manual bind hack for injector
            // - set new module if bound injector instance exists, bound injector instance enjoy new module.
            $isSetInjectorInterfaceBind = isset($module->bindings['Ray\Di\InjectorInterface']) && isset($module->bindings['Ray\Di\InjectorInterface']['*']['to'][1]);
            if ($isSetInjectorInterfaceBind) {
                $isInjectorInterfaceBoundToInjectorInstance = ($module->bindings['Ray\Di\InjectorInterface']['*']['to'][1] instanceof InjectorInterface);
                if ($isInjectorInterfaceBoundToInjectorInstance) {
                    // set new module to bound injector
                    $module->bindings['Ray\Di\InjectorInterface']['*']['to'][1]->setModule($module);
                }
            }
            $injector->setModule($module);
        }

        return $injector;
    }

    /**
     * Set binding module
     *
     * @param AbstractModule $module
     *
     * @return void
     * @throws \Aura\Di\Exception\ContainerLocked
     */
    public function setModule(AbstractModule $module)
    {
        if ($this->container->isLocked()) {
            throw new ContainerLocked;
        }
        $this->module = $module;
    }

    /**
     * Return container
     *
     * @return \Aura\Di\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->notifyPreShutdown();
    }

    /**
     * Clone
     */
    public function __clone()
    {
        $this->container = clone $this->container;
    }

    /**
     * Get a service object using binding module, optionally with overriding params.
     *
     * @param string $class  The class or interface to instantiate.
     * @param array  $params An associative array of override parameters where
     * the key the name of the constructor parameter and the value is the
     * parameter value to use.
     *
     * @return object
     * @throws Exception\NotReadable
     */
    public function getInstance($class, array $params = null)
    {
        $class = $this->removeLeadingBackSlash($class);

        // is interface ?
        $bindings = $this->module->bindings;
        try {
            $isInterface = (new ReflectionClass($class))->isInterface();
        } catch (ReflectionException $e) {
            throw new Exception\NotReadable($class);
        }
        list($config, $setter, $definition) = $this->config->fetch($class);
        $interfaceClass = $isSingleton = false;
        if ($isInterface) {
            $bound = $this->getBoundClass($bindings, $definition, $class);
            if (is_object($bound)) {

                return $bound;
            }
            list($class, $isSingleton, $interfaceClass) = $bound;
            list($config, $setter, $definition) = $this->config->fetch($class);
        }

        // annotation dependency
        /* @var $definition \Ray\Di\Definition */
        $hasDirectBinding =  isset($this->module->bindings[$class]);
        if ($definition->hasDefinition() || $hasDirectBinding) {
            list($config, $setter) = $this->bindModule($setter, $definition);
        }

        $params = is_null($params) ? $config : array_merge($config, (array) $params);
        // lazy-load params as needed
        foreach ($params as $key => $val) {
            if ($params[$key] instanceof Lazy) {
                $params[$key] = $params[$key]();
            }
        }

        // check provision
        $this->checkNotBound($class, $params, $this->module);

        // create the new instance
        $object = call_user_func_array(
                        [$this->config->getReflect($class), 'newInstance'],
                        $params
        );
        // call setters after creation
        foreach ($setter as $method => $value) {
            // does the specified setter method exist?
            if (method_exists($object, $method)) {
                if (!is_array($value)) {
                    // call the setter
                    $object->$method($value);
                } else {
                    call_user_func_array([$object, $method], $value);
                }
            }
        }
        $module = $this->module;
        $bind = $module($class, new $this->bind);
        /* @var $bind \Ray\Aop\Bind */
        if ($bind->hasBinding() === true) {
            $object = new Weaver($object, $bind);
        }
        // injection logging
        if ($this->log) {
            $this->log->log($class, $params, $setter, $object, $bind);
        }
        // set life cycle
        if ($definition) {
            $this->setLifeCycle($object, $definition);
        }
        // set singleton object
        if ($isSingleton) {
            $this->container->set($interfaceClass, $object);
        }

        return $object;
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
     * @param $bindings
     * @param mixed  $definition
     * @param string $class
     *
     * @return mixed class | object
     * @throws Exception\Binding
     */
    private function getBoundClass($bindings, $definition, $class)
    {
        if (! isset($bindings[$class]['*']['to'][0])) {
            throw new Binding($class);
        }
        $toType = $bindings[$class]['*']['to'][0];
        $isToProviderBinding = ($toType === AbstractModule::TO_PROVIDER);
        if ($isToProviderBinding) {
            $provider = $bindings[$class]['*']['to'][1];

            return $this->getInstance($provider)->get();
        }

        $inType = isset($bindings[$class]['*'][AbstractModule::IN])
        ? $bindings[$class]['*'][AbstractModule::IN] : null;
        $isSingleton = $inType === Scope::SINGLETON || $definition['Scope'] == Scope::SINGLETON;
        $interfaceClass = $class;

        if ($isSingleton && $this->container->has($interfaceClass)) {
            $object = $this->container->get($interfaceClass);
            return $object;
        }

        $class = ($toType === AbstractModule::TO_CLASS) ? $bindings[$class]['*']['to'][1] : $class;

        return [$class, $isSingleton, $interfaceClass];
    }

    /**
     * Return parameter using TO_CONSTRUCTOR
     *
     * 1) If parameter is provided, return. (check)
     * 2) If parameter is NOT provided and TO_CONSTRUCTOR binding is available, return parameter with it
     * 3) No binding found, throw exception.
     *
     * @param string $class
     * @param array          &$params
     * @param AbstractModule $module
     *
     * @return void
     * @throws Exception\NotBound
     */
    private function checkNotBound($class, array &$params, AbstractModule $module)
    {
        $ref = method_exists($class, '__construct') ? new ReflectionMethod($class, '__construct') : false;
        if ($ref === false) {
            return;
        }
        $parameters = $ref->getParameters();
        foreach ($parameters as $index => $parameter) {
            /* @var $parameter \ReflectionParameter */

            // has binding ?
            $params = array_values($params);
            if (! isset($params[$index])) {
                $hasConstructorBinding = ($module[$class]['*'][AbstractModule::TO][0] === AbstractModule::TO_CONSTRUCTOR);
                if ($hasConstructorBinding) {
                    $params[$index] = $module[$class]['*'][AbstractModule::TO][1][$parameter->name];
                    continue;
                }
                // has constructor default value ?
                if ($parameter->isDefaultValueAvailable() === true) {
                    continue;
                }
                // is typehint class ?
                $class = $parameter->getClass();
                if (! $class->isInterface() && $class) {
                    $params[$index] = $this->getInstance($class->getName());
                    continue;
                }
                throw new Exception\NotBound("Bind not found. argument #{$index}(\${$parameter->name}) in {$class} constructor.");
            }

            // has default value ?
        }
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
        if (! is_null($definition[Definition::PRE_DESTROY])) {
            $this->preDestroyObjects->attach($instance, $definition[Definition::PRE_DESTROY]);
        }

    }

    /**
     * Return dependency using modules.
     *
     * @param array          $setter
     * @param Definition     $definition
     *
     * @return array <$constructorParams, $setter>
     * @throws Exception\Binding
     */
    private function bindModule(array $setter, Definition $definition)
    {
        // @return array [AbstractModule::TO => [$toMethod, $toTarget]]
        $container = $this->container;
        /* @var $forge \Ray\Di\Forge */
        $injector = $this;
        $getInstance = function($in, $bindingToType, $target) use ($container, $definition, $injector) {
            if ($in === Scope::SINGLETON && $container->has($target)) {
                $instance = $container->get($target);

                return $instance;
            }
            switch ($bindingToType) {
                case AbstractModule::TO_CLASS:
                    $instance = $injector->getInstance($target);
                    break;
                case AbstractModule::TO_PROVIDER:
                    $provider = $injector->getInstance($target);
                    $instance = $provider->get();
                    break;
            }
            if ($in === Scope::SINGLETON) {
                $container->set($target, $instance);
            }

            return $instance;
        };
        // main
        $setterDefinitions = (isset($definition[Definition::INJECT][Definition::INJECT_SETTER])) ? $definition[Definition::INJECT][Definition::INJECT_SETTER] : null;
        if ($setterDefinitions !== null) {
            $injected = [];
            foreach ($setterDefinitions as $setterDefinition) {
                try {
                    $injected[] = $this->bindMethod($setterDefinition, $definition, $getInstance);
                } catch (OptionalInjectionNotBound $e) {
                }
            }
            $setter = [];
            foreach ($injected as $item) {
                $setterMethod = $item[0];
                $object = (count($item[1]) === 1 && $setterMethod !== '__construct') ? $item[1][0] : $item[1];
                $setter[$setterMethod] = $object;
            }
        }
        // constructor injection ?
        if (isset($setter['__construct'])) {
            $params = $setter['__construct'];
            unset($setter['__construct']);
        } else {
            $params = [];
        }
        $result = [$params, $setter];

        return $result;
    }

    /**
     * Bind method
     *
     * @param array      $setterDefinition
     * @param Definition $definition
     * @param Callable   $getInstance
     *
     * @return array
     */
    private function bindMethod(array $setterDefinition, Definition $definition, Callable $getInstance)
    {
        list($method, $settings) = each($setterDefinition);

        array_walk($settings, [$this, 'bindOneParameter'], [$definition, $getInstance]);

        return [$method, $settings];
    }

    /**
     * Set one parameter with definition, or JIT binding.
     *
     * @param string &$param
     * @param string $key
     * @param array  $userData
     *
     * @return void
     * @throws Exception\OptionalInjectionNotBound
     */
    private function bindOneParameter(&$param, $key, array $userData)
    {
        list(, $getInstance) = $userData;
        $annotate = $param[Definition::PARAM_ANNOTATE];
        $typeHint = $param[Definition::PARAM_TYPEHINT];
        $hasTypeHint = isset($this->module[$typeHint])
        && isset($this->module[$typeHint][$annotate])
        && ($this->module[$typeHint][$annotate] !== []);
        $binding = $hasTypeHint ? $this->module[$typeHint][$annotate] : false;
        if ($binding === false || isset($binding[AbstractModule::TO]) === false) {
            // default binding by @ImplementedBy or @ProviderBy
            $binding = $this->jitBinding($param, $typeHint, $annotate);
            if ($binding === self::OPTIONAL_BINDING_NOT_BOUND) {
                throw new OptionalInjectionNotBound($key);
            }
        }
        list($bindingToType, $target) = $binding[AbstractModule::TO];
        if ($bindingToType === AbstractModule::TO_INSTANCE) {
            $param = $target;

            return;
        } elseif ($bindingToType === AbstractModule::TO_CALLABLE) {
            /* @var $target \Closure */
            $param = $target();

            return;
        }
        if (isset($binding[AbstractModule::IN])) {
            $in = $binding[AbstractModule::IN];
        } else {
            list($param,, $definition) = $this->config->fetch($typeHint);
            $in = isset($definition[Definition::SCOPE]) ? $definition[Definition::SCOPE] : Scope::PROTOTYPE;
        }
        /* @var $getInstance \Closure */
        $param = $getInstance($in, $bindingToType, $target);
    }

    /**
     * JIT binding
     *
     * @param array  $param
     * @param string $typeHint
     * @param string $annotate
     *
     * @return array
     * @throws Exception\Binding
     */
    private function jitBinding(array $param, $typeHint, $annotate)
    {
        $typeHintBy = $param[Definition::PARAM_TYPEHINT_BY];
        if ($typeHintBy == []) {
            if ($param[Definition::OPTIONAL] === true) {
                return self::OPTIONAL_BINDING_NOT_BOUND;
            }
            throw new Exception\Binding("$typeHint:$annotate");
        }
        if ($typeHintBy[0] === Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY) {
            return [AbstractModule::TO => [AbstractModule::TO_CLASS, $typeHintBy[1]]];
        }

        return [AbstractModule::TO => [AbstractModule::TO_PROVIDER, $typeHintBy[1]]];
    }

    /**
     * Lock
     *
     * Lock the Container so that configuration cannot be accessed externally,
     * and no new service definitions can be added.
     *
     * @return void
     */
    public function lock()
    {
        $this->container->lock();
    }

    /**
     * Lazy new
     *
     * Returns a Lazy that creates a new instance. This allows you to replace
     * the following idiom:
     *
     * @param string $class  The type of class of instantiate.
     * @param array  $params Override parameters for the instance.
     *
     * @return Lazy A lazy-load object that creates the new instance.
     */
    public function lazyNew($class, array $params = [])
    {
        return $this->container->lazyNew($class, $params);
    }

    /**
     * Magic get to provide access to the Config::$params and $setter
     * objects.
     *
     * @param string $key The property to retrieve ('params' or 'setter').
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->container->__get($key);
    }

    /**
     * Set params or setter
     *
     * @param string $key
     * @param mixed  $val
     *
     * @return $this
     */
    public function __set($key, $val)
    {
        $this->$key = $val;

        return $this;
    }

    /**
     * Return module information.
     *
     * @return string
     */
    public function __toString()
    {
        $result = (string) ($this->module);

        return $result;
    }
}
