<?php

namespace Ray\Compiler;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class FakeToBindSingletonModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeRobotInterface::class)->to(FakeRobot::class)->in(Scope::SINGLETON);
    }
}
