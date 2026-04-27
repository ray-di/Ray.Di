<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeToNullModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(FakeRobotInterface::class)->toNull();
    }
}
