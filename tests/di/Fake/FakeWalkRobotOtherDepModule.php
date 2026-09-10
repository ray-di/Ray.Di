<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeWalkRobotOtherDepModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeWalkRobot::class);
        $this->bind(FakeRobotInterface::class)->to(FakeRobot::class);
        $this->bind(FakeLegInterface::class)->toProvider(FakeWalkRobotLegProviderWithOtherDep::class);
    }
}
