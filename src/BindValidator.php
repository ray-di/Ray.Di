<?php

/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\Exception\InvalidProvider;
use Ray\Di\Exception\InvalidType;
use Ray\Di\Exception\NotFound;

final class BindValidator
{
    /**
     * @param string $interface
     */
    public function constructor($interface)
    {
        if ($interface && ! interface_exists($interface)) {
            throw new NotFound($interface);
        }
    }

    /**
     * @param string $interface
     * @param string $class
     */
    public function to($interface, $class)
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
     * @param string $provider
     *
     * @return $this
     * @throws NotFound
     */
    public function toProvider($provider)
    {
        if (! class_exists($provider)) {
            throw new NotFound($provider);
        }
        if (! (new \ReflectionClass($provider))->implementsInterface('Ray\Di\ProviderInterface')) {
            throw new InvalidProvider($provider);
        }
    }
}
