<?php
namespace Ray\Di;

class FakeConcreteClass
{
    public function __construct(FakeAbstractClass $class)
    {
        unset($class);
    }
}
