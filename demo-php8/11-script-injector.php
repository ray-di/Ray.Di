<?php

declare(strict_types=1);

use Composer\Autoload\ClassLoader;
use Ray\Compiler\DiCompiler;
use Ray\Compiler\ScriptInjector;
use Ray\Di\Injector;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var ClassLoader $loader */
$loader->addPsr4('', __DIR__ . '/finder');

$start = microtime(true);
$injector = new Injector(new FinderModule());
$movieLister = $injector->getInstance(MovieListerInterface::class);
assert($movieLister instanceof MovieLister);
$time1 = microtime(true) - $start;

// compile
$tmpDir = __DIR__ . '/tmp';
$compiler = new DiCompiler(new FinderModule(), $tmpDir);
$compiler->compile();
$scriptInjector = new ScriptInjector($tmpDir);
$movieLister2 = $scriptInjector->getInstance(MovieListerInterface::class);

// script injector
$start = microtime(true);
$movieLister2 = $scriptInjector->getInstance(MovieListerInterface::class);
assert($movieLister2 instanceof MovieLister);
$time2 = microtime(true) - $start;

$works = $movieLister instanceof MovieListerInterface;
echo $works ? 'It works!' : 'It DOES NOT work!';
echo ' [Script injector] x' . round($time1 / $time2) . ' times faster.' . PHP_EOL;
