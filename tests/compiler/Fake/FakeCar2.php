<?php

declare(strict_types=1);

namespace Ray\Compiler;

class FakeCar2 extends FakeCar
{
    public $robot;

    public function __construct(?FakeRobot $robot = null)
    {
        $this->robot = $robot;
    }
}
