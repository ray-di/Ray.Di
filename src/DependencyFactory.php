<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind;

final class DependencyFactory implements ProviderInterface, \Serializable
{
    /**
     * @var string
     */
    private $hash;

    /**
     * @var string
     */
    private $class;

    /**
     * @var array
     */
    private $args = [];

    /**
     * @var object
     */
    private $instance;

    /**
     * @var array
     */
    private $setters = [];

    /**
     * @var CompilationLogger
     */
    private $logger;

    /**
     * @var DependencyFactory[]
     */
    private $interceptors;

    /**
     * @var string
     */
    private $postConstruct;

    /**
     * @param object            $object
     * @param array             $args
     * @param array             $setter
     * @param CompilationLogger $logger
     */
    public function __construct(
        $object,
        array $args,
        array $setter,
        CompilationLogger $logger
    ) {
        $this->class = get_class($object);
        $this->hash = $logger->getObjectHash($object);
        $this->args = $args;
        $this->setters = $setter;
        $this->logger = $logger;
    }

    /**
     * @param array $interceptors
     */
    public function setInterceptors(array $interceptors)
    {
        $this->interceptors = $interceptors;
    }

    /**
     * @param string $postConstruct
     */
    public function setPostConstruct($postConstruct)
    {
        $this->postConstruct = $postConstruct;
    }

    /**
     * @return object|\Ray\Aop\Compiler
     */
    public function get()
    {
        // is singleton ?
        if ($this->instance !== null) {
            return $this->instance;
        }
        // create object and inject dependencies
        $instance = $this->newInstance();

        // provider ?
        if ($instance instanceof ProviderInterface) {
            $instance = $instance->get();
        }
        $this->instance = $instance;

        // @PostConstruct
        if ($this->postConstruct) {
            $instance->{$this->postConstruct}();
        }
        // interceptor ?
        if ($this->interceptors) {
            $this->bindInterceptor();
        }

        return $instance;
    }

    /**
     * @return object
     */
    private function newInstance()
    {
        // constructor injection
        $args = $this->args;
        foreach ($args as &$arg) {
            if ($arg instanceof DependencyReference) {
                $arg = $arg->get();
            }
        }
        $instance = (new \ReflectionClass($this->class))->newInstanceArgs($args);

        // setter injection
        $setters = $this->setters;
        foreach ($setters as $method => &$args) {
            foreach ($args as &$arg) {
                if ($arg instanceof DependencyReference) {
                    $arg = $arg->get();
                }
            }
            call_user_func_array([$instance, $method], $args);
        }

        return $instance;
    }

    /**
     * @return void
     */
    private function bindInterceptor()
    {
        $interceptors = $this->interceptors;
        foreach ($interceptors as &$methodInterceptors) {
            foreach ($methodInterceptors as &$methodInterceptor) {
                if ($methodInterceptor instanceof DependencyReference) {
                    $methodInterceptor = $methodInterceptor->get();
                }
            }
        }
        $this->instance->rayAopBind = new Bind($interceptors);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->hash;
    }

    public function serialize()
    {
        $serialized = serialize(
            [
                $this->hash,
                $this->class,
                $this->args,
                $this->setters,
                $this->logger,
                $this->interceptors,
                $this->postConstruct
            ]
        );

        return $serialized;
    }

    public function unserialize($serialized)
    {
        list(
            $this->hash,
            $this->class,
            $this->args,
            $this->setters,
            $this->logger,
            $this->interceptors,
            $this->postConstruct
        ) = unserialize($serialized);
    }
}
