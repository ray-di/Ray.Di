<?php

namespace Ray\Compiler;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class FakeToInstanceSingletonModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeRobotInterface::class)->toInstance(new FakeRobot)->toInstance(Scope::SINGLETON);
    }
}
