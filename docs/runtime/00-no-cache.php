<?php

namespace Ray\Di\Sample;

use Ray\Di\Injector;

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require __DIR__ . '/src.php';

$injector = Injector::create([new MovieListerModule]);
foreach (range(1, 1000) as $i) {
    $movieLister = $injector->getInstance('Ray\Di\Sample\MovieListerInterface');
}
/** @var $movieLister \Ray\Di\Sample\MovieListerInterface */

$works = ($movieLister->finder instanceof MovieFinder);
echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
