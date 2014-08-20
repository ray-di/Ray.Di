<?php

namespace Ray\Di\Sample;

use MovieApp\Finder;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

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
     * @Inject
     * @Named("rightLeg=right,leftLeg=left")
     */
    public function __construct(LegInterface $rightLeg, LegInterface $leftLeg)
    {
        $this->leftLeg = $leftLeg;
        $this->rightLeg = $rightLeg;
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

$works = ($robot->rightLeg->name === 'RIGHT');
echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
