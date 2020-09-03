<?php
namespace Ray\Di;

class FakeBuiltin
{
    public $injector;

    public function __construct(InjectorInterface $Injector)
    {
        $this->injector = $Injector;
    }
}
