<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * Module with explicit class binding to Singleton scope (not via interceptor).
 */
class FakeSingletonClassModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(FakeRobotInterface::class)->to(FakeRobot::class)->in(Scope::SINGLETON);
    }
}
