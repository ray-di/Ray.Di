<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;

class FakeToInstancePrototypeModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeRobotInterface::class)->toInstance(new FakeRobot());
    }
}
