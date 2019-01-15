<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Exception\Unbound;

final class SetterMethod
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var Arguments
     */
    private $arguments;

    /**
     * Is optional binding ?
     *
     * @var bool
     */
    private $isOptional = false;

    public function __construct(\ReflectionMethod $method, Name $name)
    {
        $this->method = $method->name;
        $this->arguments = new Arguments($method, $name);
    }

    /**
     * @param object $instance
     *
     * @throws Unbound
     * @throws \Exception
     */
    public function __invoke($instance, Container $container)
    {
        try {
            $parameters = $this->arguments->inject($container);
        } catch (Unbound $e) {
            if ($this->isOptional) {
                return;
            }

            throw $e;
        }
        \call_user_func_array([$instance, $this->method], $parameters);
    }

    public function setOptional()
    {
        $this->isOptional = true;
    }
}
