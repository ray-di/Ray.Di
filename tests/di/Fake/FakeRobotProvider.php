<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeRobotProvider implements ProviderInterface
{
    public function get()
    {
        return new FakeRobot();
    }
}
