<?php

use Ray\Di\Injector;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/finder_module.php';

$start = microtime(true);
$injector = new Injector(new FinderModule);
/* @var $movieLister MovieLister */
$movieLister = $injector->getInstance(MovieListerInterface::class);
$time1 = microtime(true) - $start;

// save file cache
file_put_contents(__FILE__ . '.cache', serialize(new Injector(new FinderModule)));

// cached injector
$start = microtime(true);
$injector = unserialize(file_get_contents(__FILE__ . '.cache'));
/* @var $movieLister2 MovieLister */
$movieLister2 = $injector->getInstance(MovieListerInterface::class);
$time2 = microtime(true) - $start;

$works = $movieLister instanceof MovieListerInterface;
echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
echo 'x' . round($time1 / $time2) . ' times faster.' . PHP_EOL;
