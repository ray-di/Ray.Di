<?php

namespace Ray\Compiler;

class FakeContextualRobot implements FakeRobotInterface
{
    public $context;

    public function __construct($context)
    {
        $this->context = $context;
    }
}
