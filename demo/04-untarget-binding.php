<?php

declare(strict_types=1);

use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\Scope;

require dirname(__DIR__) . '/vendor/autoload.php';

class Finder
{
}

class FinderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(Finder::class)->in(Scope::SINGLETON);
    }
}

$injector = new Injector(new FinderModule());
$finder1 = $injector->getInstance(Finder::class);
$finder2 = $injector->getInstance(Finder::class);
$works = spl_object_hash($finder1) === spl_object_hash($finder2);

echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
