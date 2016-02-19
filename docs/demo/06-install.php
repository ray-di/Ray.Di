<?php

namespace Ray\Di\Demo;

use Ray\Di\AbstractModule;
use Ray\Di\Injector;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/src/modules.php';

class InstallModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new LinkedBindingModule);
        $this->install(new ProviderBindingModule);
        $this->install(new BindingAnnotationModule);
        $this->install(new ConstructorBindingModule);
        $this->bind(RobotInterface::class)->to(Robot::class);
    }
}

$injector = new Injector(new InstallModule);
$robot = $injector->getInstance(RobotInterface::class);
/* @var $robot Robot */
$works = $robot->isReady === true;

echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
