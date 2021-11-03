<?php

declare(strict_types=1);

namespace Ray\Di;

interface InjectorInterface
{
    /**
     * Return instance by interface + name (interface namespace)
     *
     * @param string $name
     * @psalm-param ''|class-string<T> $interface
     * @phpstan-param string $interface
     *
     * @return mixed
     * @psalm-return (T is class-string ? T : mixed)
     *
     * @psalm-template T of object
     */
    public function getInstance($interface, $name = Name::ANY);  // @phpstan-ignore-line
}
