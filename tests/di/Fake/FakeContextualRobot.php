<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeContextualRobot implements FakeRobotInterface
{
    public $context;

    public function __construct($context)
    {
        $this->context = $context;
    }
}
