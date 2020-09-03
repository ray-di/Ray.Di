<?php
namespace Ray\Di;

class FakeToConstructorRobot implements FakeRobotInterface
{
    public $leg;

    public $tmpDir;

    public $engine;

    public function __construct(FakeLegInterface $leg, $tmpDir)
    {
        $this->leg = $leg;
        $this->tmpDir = $tmpDir;
    }

    public function setEngine(FakeEngineInterface $engine): void
    {
        $this->engine = $engine;
    }
}
