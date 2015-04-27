<?php

namespace Ray\Di\Demo;

use Ray\Compiler\DiCompiler;
use Ray\Compiler\ScriptInjector;
use Ray\Di\Injector;
use Ray\Di\AbstractModule;

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

$start = microtime(true);
$injector = new Injector(new InstallModule);
$robot1 = $injector->getInstance(RobotInterface::class);
$time1 = microtime(true) - $start;

$tmpDir =  __DIR__ . '/tmp';
$compiler = new DiCompiler(new InstallModule, $tmpDir);
$compiler->compile();

$start = microtime(true);
$scriptInjector = new ScriptInjector($tmpDir);
$robot2 = $scriptInjector->getInstance(RobotInterface::class);
$time2 = microtime(true) - $start;

$works = $robot1->isReady === true && $robot2->isReady === true;
echo ($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
echo 'x' . round($time1 / $time2) . ' times faster.' . PHP_EOL;
