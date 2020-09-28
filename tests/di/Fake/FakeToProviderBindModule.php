<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeToProviderBindModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeRobotInterface::class)->toProvider(FakeRobotProvider::class);
    }
}
