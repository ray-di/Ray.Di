<?php

namespace Ray\Di\Sample;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\Injector;
use Ray\Di\CacheInjector;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require __DIR__ . '/src.php';

$injector = function()  {
    return Injector::create([new MovieListerModule], new ArrayCache,  __DIR__ . '/tmp');
};
$initialization = function() {
    // initialize per system startup (not per each request)
};

$tmpDir = __DIR__ . '/tmp';
$cache = new FilesystemCache($tmpDir);
$cacheKey = 'context-key';

$injector = new CacheInjector($injector, $initialization, $cacheKey, $cache);
foreach (range(1, 1000) as $i) {
    $movieLister = $injector->getInstance('Ray\Di\Sample\MovieListerInterface');
}
/** @var $movieLister \Ray\Di\Sample\MovieListerInterface */

$works = ($movieLister->finder instanceof MovieFinder);
echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
