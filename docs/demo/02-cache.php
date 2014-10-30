<?php

namespace Ray\Di\Demo;

use Ray\Di\Injector;

require dirname(__DIR__) . '/src.php';

$cache = __FILE__ . '.cache';
if (! file_exists($cache)) {
    $injector = new Injector(new ListerModule);
    file_put_contents($cache, serialize($injector));
    // save
    exit(0);
}
// load
$injector = unserialize(file_get_contents($cache));
$movieLister = $injector->getInstance(MovieListerInterface::class);

$works = ($movieLister->finder instanceof Finder);
echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
