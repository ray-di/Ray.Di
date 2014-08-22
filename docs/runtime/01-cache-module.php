<?php

namespace Ray\Di\Sample;

use Ray\Di\Injector;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\CacheableModule;
use Ray\Di\ModuleCacheInjector;


require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require __DIR__ . '/src.php';

$tmpDir = __DIR__ . '/tmp';
$cache = new FilesystemCache($tmpDir);
$cacheKey = 'cache-key';
$moduleProvider = function() {return new MovieListerModule;};
$injector = ModuleCacheInjector::create($moduleProvider, $cache, $cacheKey, $tmpDir);
foreach (range(1, 1000) as $i) {
    $movieLister = $injector->getInstance('Ray\Di\Sample\MovieListerInterface');
}
/** @var $movieLister \Ray\Di\Sample\MovieListerInterface */

$works = ($movieLister->finder instanceof MovieFinder);
echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
