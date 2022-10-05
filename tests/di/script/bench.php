<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Compiler\DiCompiler;
use Ray\Compiler\ScriptInjector;

use function assert;
use function microtime;
use function printf;
use function range;
use function serialize;
use function unserialize;

require __DIR__ . '/bootstrap.php';
$n = 20;

$injector = new Injector(new FakeCarModule());
$serialize = serialize($injector);

$timer = microtime(true);
foreach (range(1, $n) as $i) {
    $injector = new Injector(new FakeCarModule());
    $injector->getInstance(FakeCarInterface::class);
}

$timer1 = microtime(true) - $timer;

$timer = microtime(true);
$injector = unserialize($serialize);
assert($injector instanceof InjectorInterface);
foreach (range(1, $n) as $i) {
    $injector->getInstance(FakeCarInterface::class);
}

$timer2 = microtime(true) - $timer;

$compiler = new DiCompiler(new FakeCarModule(), __DIR__ . '/tmp');
$compiler->compile();
$timer = microtime(true);
$injector = new ScriptInjector(__DIR__ . '/tmp');
foreach (range(1, $n) as $i) {
    $injector->getInstance(FakeCarInterface::class);
}

$timer3 = microtime(true) - $timer;

// Microsecond per inject
printf("micro second per inject (MPI):%f speed:x%d\n", $timer2 / $n, $timer1 / $timer2);
printf("micro second per inject (MPI):%f speed:x%d\n", $timer3 / $n, $timer1 / $timer3);
