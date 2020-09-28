<?php

namespace Ray\Compiler;

class FakeDependSingleton
{
    /**
     * @var FakeRobotInterface
     */
    public $robot;

    public function __construct(FakeRobotInterface $robot)
    {
        $this->robot = $robot;
    }
}
