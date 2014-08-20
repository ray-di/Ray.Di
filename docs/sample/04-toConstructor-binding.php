<?php

namespace Ray\Di\Sample;

use MovieApp\Finder;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

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

/**
 * Third-party Robot class (no annotations)
 */
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
        $this->bind('Ray\Di\Sample\Robot')->toConstructor(['leftLeg' => new LeftLeg(), 'rightLeg' => new RightLeg()]);
    }
}

$injector = Injector::create([new RobotModule]);
$robot = $injector->getInstance('Ray\Di\Sample\Robot');
/** @var $robot \Ray\Di\Sample\Robot */

$works = ($robot->rightLeg->name === 'RIGHT');
echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
