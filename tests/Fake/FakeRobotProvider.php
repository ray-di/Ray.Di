<?php
namespace Ray\Di;

class FakeRobotProvider implements ProviderInterface
{
    public function get()
    {
        return new FakeRobot;
    }
}
