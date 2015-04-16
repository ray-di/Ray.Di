<?php

namespace Ray\Di\Demo;

use Ray\Di\Di\Inject;

class QualifierRobot
{
    public $rightLeg;

    public $leftLeg;

    /**
     * @Inject
     * @Right
     */
    public function setRightLeg(LegInterface $rightLeg)
    {
        $this->rightLeg = $rightLeg;
    }

    /**
     * @Inject
     * @Left
     */
    public function setLeftLeg(LegInterface $leftLeg)
    {
        $this->leftLeg = $leftLeg;
    }
}
