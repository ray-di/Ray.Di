<?php

namespace Ray\Di\Demo;

require __DIR__ . '/bootstrap.php';

use Ray\Di\Injector;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class UntargetBindingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(Php::class)->in(Scope::SINGLETON);
    }
}

$injector = new Injector(new UntargetBindingModule);
$php1 = $injector->getInstance(Php::class);
$php2 = $injector->getInstance(Php::class);
/** @var $phpRobot Robot */
$works = spl_object_hash($php1) === spl_object_hash($php2);

echo ($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
