<?php

namespace Ray\Di\Demo;

use Ray\Di\AbstractModule;
use Ray\Di\Injector;

require __DIR__ . '/bootstrap.php';

class BindingAnnotationNamedModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(LegInterface::class)->annotatedWith('left')->to(LeftLeg::class);
        $this->bind(LegInterface::class)->annotatedWith('right')->to(RightLeg::class);
    }
}

$injector = new Injector(new BindingAnnotationNamedModule);
$robot = $injector->getInstance(NamedRobot::class);
/* @var $robot NamedRobot */
$works = ($robot->leftLeg instanceof LeftLeg);

echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
