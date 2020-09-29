<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeConcreteClass
{
    public function __construct(FakeAbstractClass $class)
    {
        unset($class);
    }
}
