<?php

namespace Ray\Di\Demo;

use Ray\Di\Injector;
use Ray\Di\AbstractModule;

require __DIR__ . '/bootstrap.php';

class LinkedBindingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(LangInterface::class)->to(Php::class);
        $this->bind(ComputerInterface::class)->to(Computer::class);
    }
}

$injector = new Injector(new LinkedBindingModule);
$computer = $injector->getInstance(ComputerInterface::class);
/** @var $computer Computer */
$works = ($computer->lang instanceof Php);

echo ($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
