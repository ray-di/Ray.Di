<?php

/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\Exception\InvalidType;
use Ray\Di\Exception\NotFound;
use Ray\Di\Exception\InvalidProvider;

final class BindValidator
{
    /**
     * @param $interface
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
        $notExistClass = ! class_exists($class);
        $notImplementedClass = interface_exists($interface) && ! (new \ReflectionClass($class))->implementsInterface($interface);
        if ($notExistClass || $notImplementedClass) {
            throw new InvalidType($class);
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
        if (! (new \ReflectionClass($provider))->implementsInterface(ProviderInterface::class)) {
            throw new InvalidProvider($provider);
        }
    }
}
