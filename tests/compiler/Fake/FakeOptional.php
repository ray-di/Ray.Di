<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\Di\Inject;

class FakeOptional
{
    public $robot = null;

    /**
     * @Inject(optional=true)
     */
    public function setOptionalRobot(FakeRobotInterface $robot)
    {
        $this->robot = $robot;
    }
}
