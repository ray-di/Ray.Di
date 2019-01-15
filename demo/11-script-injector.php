<?php

declare(strict_types=1);

use Ray\Compiler\DiCompiler;
use Ray\Compiler\ScriptInjector;
use Ray\Di\Injector;

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/finder_module.php';

$start = microtime(true);
$injector = new Injector(new FinderModule);
/* @var $movieLister MovieLister */
$movieLister = $injector->getInstance(MovieListerInterface::class);
$time1 = microtime(true) - $start;

// compile
$tmpDir = __DIR__ . '/tmp';
$compiler = new DiCompiler(new FinderModule, $tmpDir);
$compiler->compile();
$scriptInjector = new ScriptInjector($tmpDir);
$movieLister2 = $scriptInjector->getInstance(MovieListerInterface::class);

// script injector
$start = microtime(true);
/* @var $movieLister2 MovieLister */
$movieLister2 = $scriptInjector->getInstance(MovieListerInterface::class);
$time2 = microtime(true) - $start;

$works = $movieLister instanceof MovieListerInterface;
echo $works ? 'It works!' : 'It DOES NOT work!';
echo ' [Script injector] x' . round($time1 / $time2) . ' times faster.' . PHP_EOL;
