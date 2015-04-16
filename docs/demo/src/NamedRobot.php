<?php

namespace Ray\Di\Demo;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class NamedRobot
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
