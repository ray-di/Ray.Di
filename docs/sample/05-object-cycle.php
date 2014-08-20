<?php

namespace Ray\Di\Sample;

use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

interface LegInterface
{
}

class RightLeg implements LegInterface
{
    public $name = "RIGHT";
}

class LeftLeg implements LegInterface
{
    public $name = "LEFT";
}

class Robot
{
    /**
     * @var LegInterface
     */
    public $rightLeg;

    /**
     * @var LegInterface
     */
    public $leftLeg;

    /**
     * @param LegInterface $rightLeg
     *
     * @Inject
     * @Named("right")
     */
    public function setRightLeg(LegInterface $rightLeg)
    {
        $this->rightLeg = $rightLeg;
    }

    /**
     * @param LegInterface $rightLeg
     *
     * @Inject
     * @Named("left")
     */
    public function setLeftLeg(LegInterface $leftLeg)
    {
        $this->leftLeg = $leftLeg;
    }

    /**
     * @return string
     *
     * @PostConstruct
     */
    public function init()
    {
        return "{$this->rightLeg->name} leg and {$this->leftLeg->name} leg are set.";
    }
}

class RobotModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Sample\LegInterface')->annotatedWith('right')->to('Ray\Di\Sample\RightLeg');
        $this->bind('Ray\Di\Sample\LegInterface')->annotatedWith('left')->to('Ray\Di\Sample\LeftLeg');
    }
}

$injector = Injector::create([new RobotModule]);
$robot = $injector->getInstance('Ray\Di\Sample\Robot');
/** @var $robot \Ray\Di\Sample\Robot */

$works = ($robot->init() === 'RIGHT leg and LEFT leg are set.');
echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
