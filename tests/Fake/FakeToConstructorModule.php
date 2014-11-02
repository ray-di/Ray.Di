<?php

namespace Ray\Di;

class FakeAopModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeRobotInterface::class)->to(FakeToConstructorRobot::class);
        $this->bind()->annotatedWith('tmp_dir')->toInstance('/tmp');
        $this->bind(FakeLegInterface::class)->annotatedWith('left')->to(FakeLeftLeg::class);
        $this->bind(FakeToConstructorRobot::class)->toConstructor('tmpDir=tmp_dir, leg=left');
    }
}
