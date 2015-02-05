<?php
namespace Ray\Di;

/**
 * @FakeConstant("class_constant_val")
 */
class FakeWalkRobot
{
    /**
     * @var FakeLegInterface
     */
    public $leftLeg;

    /**
     * @var FakeLegInterface
     */
    public $rightLeg;

    /**
     * @FakeConstant(10)
     */
    public function __construct(FakeLegInterface $rightLeg, FakeLegInterface $leftLeg)
    {
        $this->rightLeg = $rightLeg;
        $this->leftLeg = $leftLeg;
    }
}
