<?php

declare(strict_types=1);

namespace Ray\Compiler;

class FakeDependPrototype
{
    /** @var FakeCarInterface */
    public $car;

    public function __construct(FakeCarInterface $car)
    {
        $this->car = $car;
    }
}
