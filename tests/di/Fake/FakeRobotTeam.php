<?php
namespace Ray\Di;

class FakeRobotTeam
{
    public $robot1;

    public $robot2;

    public function __construct(FakeRobot $robot1, FakeRobot $robot2)
    {
        $this->robot1 = $robot1;
        $this->robot2 = $robot2;
    }
}
