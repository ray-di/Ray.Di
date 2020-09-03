<?php

namespace Ray\Compiler;

class FakeDependPrototype
{
    /**
     * @var FakeCarInterface
     */
    public $car;

    public function __construct(FakeCarInterface $car)
    {
        $this->car = $car;
    }
}
