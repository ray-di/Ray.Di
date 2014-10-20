<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\Exception\Unbound;

final class SetterMethod implements \Serializable
{
    /**
     * @var \ReflectionMethod
     */
    private $method;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * Is optional binding ?
     *
     * @var bool
     */
    private $isOptional = false;

    /**
     * @param \ReflectionMethod $method
     * @param Name              $name
     */
    public function __construct(\ReflectionMethod $method, Name $name)
    {
        $this->method = $method;
        $this->parameters = new Parameters($method, $name);
    }

    public function setOptional()
    {
        $this->isOptional = true;
    }

    /**
     * @param object    $instance
     * @param Container $container
     *
     * @throws Unbound
     * @throws \Exception
     */
    public function __invoke($instance, Container $container)
    {
        try {
            $parameters = $this->parameters->get($container);
        } catch (Unbound $e) {
            if ($this->isOptional) {
                return;
            }
            throw $e;
        }
        $this->method->invokeArgs($instance, $parameters);
    }

    public function serialize()
    {
        return serialize(
            [
                [$this->method->class, $this->method->name],
                $this->parameters,
                $this->isOptional
            ]
        );
    }

    public function unserialize($serialized)
    {
        list($method, $this->parameters, $this->isOptional) = unserialize($serialized);
        $this->method = new \ReflectionMethod($method[0], $method[1]);
    }
}
