<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Exception\InvalidProvider;
use Ray\Di\Exception\InvalidType;
use Ray\Di\Exception\NotFound;

final class BindValidator
{
    public function constructor(string $interface) : void
    {
        if ($interface && ! interface_exists($interface) && ! class_exists($interface)) {
            throw new NotFound($interface);
        }
    }

    /**
     * To validator
     *
     * @return \ReflectionClass<object>
     */
    public function to(string $interface, string $class) : \ReflectionClass
    {
        if (! class_exists($class)) {
            throw new NotFound($class);
        }
        if (interface_exists($interface) && ! (new \ReflectionClass($class))->implementsInterface($interface)) {
            throw new InvalidType("[{$class}] is no implemented [{$interface}] interface");
        }

        return new \ReflectionClass($class);
    }

    /**
     * toProvider validator
     *
     * @phpstan-param class-string $provider
     *
     * @throws NotFound
     *
     * @return \ReflectionClass<object>
     */
    public function toProvider(string $provider) : \ReflectionClass
    {
        if (! class_exists($provider)) {
            throw new NotFound($provider);
        }
        if (! (new \ReflectionClass($provider))->implementsInterface(ProviderInterface::class)) {
            throw new InvalidProvider($provider);
        }

        return new \ReflectionClass($provider);
    }
}
