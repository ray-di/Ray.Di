<?php

namespace Ray\Compiler;

use Ray\Di\InjectorInterface;

class FakeFactory implements FakeCarInterface
{
    public $injector;

    public function __construct(InjectorInterface $injector)
    {
        $this->injector = $injector;
    }
}
