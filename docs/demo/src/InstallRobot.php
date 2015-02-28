<?php

namespace Ray\Di\Demo;

use Ray\Di\Demo\Left;
use Ray\Di\Demo\Right;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class InstallRobot
{
    public $rightLeg;

    public $leftLeg;

    /**
     * @Inject
     * @Named("right")
     */
    public function setRightLeg(LegInterface $rightLeg)
    {
        $this->rightLeg = $rightLeg;
    }

    /**
     * @Inject
     * @Named("left")
     */
    public function setLeftLeg(LegInterface $leftLeg)
    {
        $this->leftLeg = $leftLeg;
    }
}
