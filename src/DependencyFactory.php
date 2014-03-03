<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

class DependencyFactory
{
    /**
     * @var string
     */
    public $hash;

    /**
     * @var string
     */
    public $class;

    /**
     * @var array
     */
    public $args = [];

    /**
     * @var object
     */
    public $instance;

    /**
     * @var array
     */
    public $setters = [];

    /**
     * @var null|string
     */
    private $providerRef;

    /**
     * @var CompileLogger
     */
    private $logger;


    /**
     * @var DependencyFactory[]
     */
    private $interceptors;

    /**
     * @param object            $object
     * @param array             $args
     * @param array             $setter
     * @param CompileLogger     $logger
     * @param ProviderInterface $provider
     */
    public function __construct(
        $object,
        array $args,
        array $setter,
        CompileLogger $logger,
        ProviderInterface $provider = null,
        array $interceptors = null
    ) {
        $this->class = get_class($object);
        $this->hash = spl_object_hash($object);
        $this->args = $args;
        $this->setters = $setter;
        $this->logger = $logger;
        $this->providerRef = is_object($provider) ? spl_object_hash($provider) : null;
        $this->interceptors = $interceptors;
    }

    public function newInstance()
    {
        // is singleton ?
        if ($this->instance) {
            return $this->instance;
        }

        // constructor injection
        foreach ($this->args as &$arg) {
            if ($arg instanceof InstanceRef) {
                $arg = $this->logger->newInstance($arg->refIndex);
            }
        }
        $instance = (new \ReflectionClass($this->class))->newInstanceArgs($this->args);

        // setter injection
        foreach ($this->setters as $method => &$args) {
            foreach ($args as &$arg) {
                if ($arg instanceof InstanceRef) {
                    $arg = $this->logger->newInstance($arg->refIndex);
                }
            }
            call_user_func_array([$instance, $method], $args);
        }

        // provider ?
        if ($instance instanceof ProviderInterface) {
            $instance = $instance->get();
        }
        $this->instance = $instance;

        // interceptor ?
        if ($this->interceptors) {
            $this->instance->rayAopBind = $this->interceptors;
        }
        return $instance;
    }
}
