<?php
/**
 *
 * This file is part of the Aura Project for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Di;

/**
 *
 * Dependency Injector.
 *
 * @package Aura.Di
 *
 */
class Injector
{
    /**
     * Config
     *
     * @var Config
     *
     */
    protected $config;

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
     * Pre-destory objects
     *
     * @var SplObjectStorage
     */
    private $preDestroyObjects;

    /**
     * Constructor
     *
     * @param string $class The class to instantiate.
     * @param AbstractModule $module Binding configuration module
     * @param array $params An associative array of override parameters where
     * the key the name of the constructor parameter and the value is the
     * parameter value to use.
     *
     * @return object
     *
     */
    public function __construct(ContainerInterface $container, AbstractModule $module)
    {
        $this->container = $container;
        $this->config = $container->getForge()->getConfig();
        $this->preDestroyObjects = new \SplObjectStorage;
        $this->module = $module;
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
     *
     */
    public function __clone()
    {
        $this->container = clone $this->container;
    }

    /**
     * Gets the injected Config object.
     *
     * @return ConfigInterface
     *
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get a service object using binding module, optionally with overriding params.
     *
     * @param string $class The class to instantiate.
     * @param AbstractModule binding module
     * @param array $params An associative array of override parameters where
     * the key the name of the constructor parameter and the value is the
     * parameter value to use.
     *
     * @return object
     *
     */
    public function getInstance($class, array $params = null)
    {
        list($config, $setter, $definition) = $this->config->fetch($class);
        // annotation-oriented dependency
        if ($definition !== array()) {
            list($config, $setter) = $this->bindModule($setter, $definition, $this->module);
        }
        $params = is_null($params) ? $config : array_merge($config, (array) $params);

        // create the new instance
        $object = call_user_func_array(
            array($this->config->getReflect($class), 'newInstance'),
            $params
        );

        // set life cycle
        if ($definition) {
            $this->setLifeCycle($object, $definition);
        }

        // call setters after creation
        foreach ($setter as $method => $value) {
            // does the specified setter method exist?
            if (method_exists($object, $method)) {
                if (!is_array($value)) {
                    // call the setter
                    $object->$method($value);
                } else {
                    call_user_func_array(array($object, $method), $value);
                }
            }
        }
        return $object;
    }

    /**
     * Notify pre-destory
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
     * @param object $instance
     * @param array  $definition
     *
     * @return void
     */
    private function setLifeCycle($instance, array $definition = null)
    {
        if (isset($definition[Definition::POST_CONSTRUCT])
        && method_exists($instance, $definition[Definition::POST_CONSTRUCT])) {
            //signal
            call_user_func(array($instance, $definition[Definition::POST_CONSTRUCT]));
        }
        if (isset($definition[Definition::PRE_DESTROY])) {
            $this->preDestroyObjects->attach($instance, $definition[Definition::PRE_DESTROY]);
        }

    }

    /**
     * Return dependency using modules
     *
     * @param object $instance
     * @param array  $definition
     * @param AbstractModule $module
     *
     * @return array <$constructorParams, $setter>
     */
    private function bindModule(array $setter, array $definition = null, AbstractModule $module)
    {
        // @return array array(AbstractModule::TO => array($toMethod, $toTarget)
        $jitBinding = function($param, $definition, $typeHint, $annotate) use ($module) {
            $typehintBy = $param[Definition::PARAM_TYPEHINT_BY];
            if ($typehintBy == array()) {
                $typehint = $param[Definition::PARAM_TYPEHINT];
                throw new Exception\InvalidBinding("$typeHint:$annotate");
            }
            if ($typehintBy[0] === Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY) {
                return array(AbstractModule::TO => array(AbstractModule::TO_CLASS, $typehintBy[1]));
            }
            return array(AbstractModule::TO => array(AbstractModule::TO_PROVIDER, $typehintBy[1]));
        };
        $container = $this->container;
        $config = $this->container->getForge()->getConfig();
        /* @var $forge Aura\Di\Forge */
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

        $bindOneParameter = function($param) use ($jitBinding, $definition, $module, $getInstance, $config) {
            $annotate = $param[Definition::PARAM_ANNOTATE];
            $typeHint = $param[Definition::PARAM_TYPEHINT];
            $binding = (isset($module[$typeHint]) && isset($module[$typeHint][$annotate]) && ($module[$typeHint][$annotate] !== array()))
                ? $module[$typeHint][$annotate] : false;
            if ($binding === false || isset($binding[AbstractModule::TO]) === false) {
                // default bindg by @ImplemetedBy or @ProviderBy
                $binding = $jitBinding($param, $definition, $typeHint, $annotate);
            }
            $bindingToType = $binding[AbstractModule::TO][0];
            $target = $binding[AbstractModule::TO][1];
            if ($bindingToType === AbstractModule::TO_INSTANCE) {
                return $target;
            } elseif ($bindingToType === AbstractModule::TO_CLOSURE) {
                return $target();
            }
            if (isset($binding[AbstractModule::IN])) {
                $in = $binding[AbstractModule::IN];
            } else {
                list($param, $setter, $definition) = $config->fetch($target);
                $in = isset($definition[Definition::SCOPE]) ? $definition[Definition::SCOPE] : Scope::PROTOTYPE;
            }
            $instance = $getInstance($in, $bindingToType, $target);
            return $instance;
        };

        $bindMethod = function ($item) use ($bindOneParameter, $definition, $module) {
            list($method, $settings) = each($item);
            $params = array_map($bindOneParameter, $settings);
            return array($method, $params);
        };
        
        // main
        $setterDefinitions = (isset($definition[Definition::INJECT][Definition::INJECT_SETTER]))
        ? $definition[Definition::INJECT][Definition::INJECT_SETTER]
        : false ;
        if ($setterDefinitions !== false) {
            $injected = array_map($bindMethod, $setterDefinitions);
            $setter = array();
            foreach ($injected as $item) {
                $setterMethod = $item[0];
                $object =  (count($item[1]) === 1 && $setterMethod !== '__construct') ? $item[1][0] : $item[1];
                $setter[$setterMethod] = $object;
            }
        }
        // constuctor injection ?
        if (isset($setter['__construct'])){
            $params = $setter['__construct'];
            unset($setter['__construct']);
        } else {
            $params = array();
        }
        return array($params, $setter);
    }
}
