<?php

namespace Ray\Compiler;

class FakeCar2 extends FakeCar
{
    public $robot;

    public function __construct(FakeRobot $robot = null)
    {
        $this->robot = $robot;
    }
}
