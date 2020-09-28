<?php

namespace Ray\Compiler;

use Ray\Di\ProviderInterface;

class FakeRobotProvider implements ProviderInterface
{
    public function get()
    {
        $robot = new FakeRobot;
//        $robot->a = new \DateTimeImmutable();

        return $robot;
    }
}
