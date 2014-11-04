<?php

namespace Ray\Di\Demo;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;

class Robot implements RobotInterface
{
    public $isReady = false;

    private $computer;

    private $rightLeg;

    private $leftLeg;
    /**
     * @Inject
     * @Named("rightLeg=right, leftLeg=left")
     */
    public function setLegs(LegInterface $rightLeg, LegInterface $leftLeg)
    {
        $this->rightLeg = $rightLeg;
        $this->leftLeg = $leftLeg;
    }

    public function __construct(ComputerInterface $computer)
    {
        $this->computer = $computer;
    }

    /**
     * @PostConstruct
     */
    public function selfCheck()
    {
        $isLegOk = $this->leftLeg instanceof LeftLeg && $this->rightLeg instanceof RightLeg;
        $isComputerOk = $this->computer instanceof ComputerInterface;
        $isPhpVersionOk = version_compare($this->computer->lang->version, '7.0', '==');
        $this->isReady = $isLegOk && $isComputerOk && $isPhpVersionOk;
    }
}