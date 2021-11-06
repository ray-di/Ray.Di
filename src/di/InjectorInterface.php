<?php

declare(strict_types=1);

namespace Ray\Di;

interface InjectorInterface
{
    /**
     * Return object graph
     *
     * @param class-string<T> $interface interface name|class name|empty-string
     * @param string          $name      interface name space
     * @psalm-param ''|class-string<T>   $interface
     * @phpstan-param ''|class-string    $interface
     *
     * @return T
     * @psalm-return   (T is object ? T : mixed)
     * @phpstan-return mixed
     *
     * @psalm-template T of object
     *
     * @see https://github.com/ray-di/Ray.Di
     */
    public function getInstance($interface, $name = Name::ANY);  // @phpstan-ignore-line
}
