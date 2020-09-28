<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;

class FakeToProviderPrototypeModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeRobotInterface::class)->toProvider(FakeRobotProvider::class);
    }
}
