<?php

declare(strict_types=1);

namespace Ray\Di;

interface InjectorInterface
{
    /**
     * Return instance by interface + name (interface namespace)
     *
     * @param string $interface
     * @param string $name
     *
     * @return mixed
     */
    public function getInstance($interface, $name = Name::ANY);
}
