<?php

namespace Ray\Di\Test\Sample;

use Ray\Di\Injector;

$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

require __DIR__ . '/src.php';

if (file_exists('cache.data')) {

    $injector = unserialize(file_get_contents('cache.data'));
    $movieLister = $injector->getInstance(MovieListerInterface::class);

    $works = ($movieLister->finder instanceof FinderInterface);
    echo (($works) ? 'Cache works !' : 'It DOES NOT work!') . PHP_EOL;
    exit(0);
}

$injector = new Injector(new ListerModule);
file_put_contents('cache.data', serialize($injector));
echo 'Cache saved.' . PHP_EOL;
