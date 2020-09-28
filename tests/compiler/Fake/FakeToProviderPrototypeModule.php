<?php

namespace Ray\Compiler;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class FakeToProviderPrototypeModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeRobotInterface::class)->toProvider(FakeRobotProvider::class);
    }
}
