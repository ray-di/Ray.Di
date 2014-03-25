<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind;

final class DependencyFactory implements ProviderInterface
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
     * @var CompileLogger
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
     * @param object        $object
     * @param array         $args
     * @param array         $setter
     * @param CompileLogger $logger
     */
    public function __construct(
        $object,
        array $args,
        array $setter,
        CompileLogger $logger
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
        if ($this->instance) {
            return $this->instance;
        }
        // constructor injection
        foreach ($this->args as &$arg) {
            if ($arg instanceof DependencyReference) {
                $arg = $arg->get();
            }
        }
        $instance = (new \ReflectionClass($this->class))->newInstanceArgs($this->args);

        // setter injection
        foreach ($this->setters as $method => &$args) {
            foreach ($args as &$arg) {
                if ($arg instanceof DependencyReference) {
                    $arg = $arg->get();
                }
            }
            call_user_func_array([$instance, $method], $args);
        }

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
            foreach ($this->interceptors as &$methodInterceptors) {
                foreach ($methodInterceptors as &$methodInterceptor) {
                    if ($methodInterceptor instanceof DependencyReference) {
                        $methodInterceptor = $methodInterceptor->get();
                    }
                }
            }
            $this->instance->rayAopBind = new Bind($this->interceptors);
        }

        return $instance;
    }

    public function __toString()
    {
        return $this->hash;
    }
}
