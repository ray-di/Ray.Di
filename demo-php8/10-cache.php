<?php

declare(strict_types=1);

use Composer\Autoload\ClassLoader;
use Ray\Di\Injector;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var ClassLoader $loader */
$loader->addPsr4('', __DIR__ . '/finder');

$start = microtime(true);
$injector = new Injector(new FinderModule());
$movieLister = $injector->getInstance(MovieListerInterface::class);
assert($movieLister instanceof MovieLister);
$time1 = microtime(true) - $start;

// save file cache
file_put_contents(__FILE__ . '.cache.php', serialize(new Injector(new FinderModule())));

// cached injector
$start = microtime(true);
$injector = unserialize(file_get_contents(__FILE__ . '.cache.php'));
$movieLister2 = $injector->getInstance(MovieListerInterface::class);
assert($movieLister2 instanceof MovieLister);
$time2 = microtime(true) - $start;

$works = $movieLister instanceof MovieListerInterface;
echo $works ? 'It works!' : 'It DOES NOT work!';
echo ' [Injector cache] x' . round($time1 / $time2) . ' times faster.' . PHP_EOL;
