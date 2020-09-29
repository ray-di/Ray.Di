<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeWalkRobotModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeLegInterface::class)->toProvider(FakeWalkRobotLegProvider::class);
    }
}
