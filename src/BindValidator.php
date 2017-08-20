<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Ray\Di\Exception\InvalidProvider;
use Ray\Di\Exception\InvalidType;
use Ray\Di\Exception\NotFound;

final class BindValidator
{
    public function constructor(string $interface)
    {
        if ($interface && ! interface_exists($interface) && ! class_exists($interface)) {
            throw new NotFound($interface);
        }
    }

    /**
     * To validator
     */
    public function to(string $interface, string $class)
    {
        if (! class_exists($class)) {
            throw new NotFound($class);
        }
        if (interface_exists($interface) && ! (new \ReflectionClass($class))->implementsInterface($interface)) {
            $msg = "[{$class}] is no implemented [{$interface}] interface";
            throw new InvalidType($msg);
        }
    }

    /**
     * toProvider validator
     *
     * @throws NotFound
     */
    public function toProvider(string $provider)
    {
        if (! class_exists($provider)) {
            throw new NotFound($provider);
        }
        if (! (new \ReflectionClass($provider))->implementsInterface(ProviderInterface::class)) {
            throw new InvalidProvider($provider);
        }
    }
}
