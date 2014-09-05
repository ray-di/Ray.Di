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
     * @var mixed
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
     * Method interceptors
     *
     * [$method1 => MethodInterceptors[], $method2 =>  MethodInterceptors[] ... ]
     *
     * @var array
     */
    private $interceptors;

    /**
     * @var string
     */
    private $postConstruct;

    /**
     * @var bool
     */
    private $isSingleton;

    /**
     * @param object            $object
     * @param array             $args
     * @param array             $setter
     * @param CompilationLogger $logger
     * @param bool              $isSingleton
     */
    public function __construct(
        $object,
        array $args,
        array $setter,
        CompilationLogger $logger,
        $isSingleton
    ) {
        $this->class = get_class($object);
        $this->hash = $logger->getObjectHash($object);
        $this->args = $args;
        $this->setters = $setter;
        $this->logger = $logger;
        $this->isSingleton = $isSingleton;
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
        if ($this->instance !== null && $this->isSingleton === true) {
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
            /** @var array $methodInterceptors */
            foreach ($methodInterceptors as &$methodInterceptor) {
                /** @var $methodInterceptor \Ray\Aop\MethodInterceptor|DependencyReference $methodInterceptor */
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

    /**
     * @return string
     */
    public function getName()
    {
        return "{$this->class}#{$this->hash}";
    }

    /**
     * @return string
     */
    public function getDependencyLog()
    {
        $constructorArgs = $this->getArgsLogText($this->args);
        $setterArgs = '';
        foreach ($this->setters as $method => $args) {
            $setterArgs .= sprintf(
                ' %s:%s',
                $method,
                $this->getArgsLogText($args)
            );
        }
        if (! $constructorArgs . $setterArgs) {
            return '';
        }
        return sprintf(
            'ref:%s __construct:%s%s',
            $this->hash,
            $constructorArgs,
            $setterArgs
        );
    }

    /**
     * @return string
     */
    public function getInterceptorLog()
    {
        if (! is_array($this->interceptors)) {
            return '';
        }
        $log = '';
        foreach ($this->interceptors as $method => $interceptorReferences) {
            $log = sprintf(
                'ref:%s %s:%s',
                $this->hash,
                $method,
                $this->getArgsLogText($interceptorReferences)
            );
        }

        return $log;
    }

    /**
     * @param mixed $args
     *
     * @return string
     */
    private function getArgsLogText($args)
    {
        foreach ($args as &$item) {
            $item =  is_array($item) ? 'array(' . count($item) . ')' : $item;
            $item = is_scalar($item) ? gettype($item) : $item;
            if ($item instanceof DependencyReference) {
                $item = (string) $item;
            }
        }
        $args = (is_array($args)) ? implode(',', $args) : getype($args);

        return $args;
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
