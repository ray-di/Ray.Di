<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeBuiltin
{
    public $injector;

    public function __construct(InjectorInterface $Injector)
    {
        $this->injector = $Injector;
    }
}
