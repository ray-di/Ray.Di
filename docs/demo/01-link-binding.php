<?php

namespace Ray\Di\Demo;

use Ray\Di\Injector;

require dirname(__DIR__) . '/src.php';

$injector = new Injector(new ListerModule);
$movieLister = $injector->getInstance(MovieListerInterface::class);

$works = ($movieLister->finder instanceof Finder);
echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
