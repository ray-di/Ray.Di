<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Compiler\DiCompiler;
use Ray\Compiler\ScriptInjector;

require __DIR__ . '/bootstrap.php';
$n = 20;

$injector = new Injector(new FakeCarModule);
$serialize = serialize($injector);

$timer = microtime(true);
foreach (range(1, $n) as $i) {
    $injector = new Injector(new FakeCarModule);
    $injector->getInstance(FakeCarInterface::class);
}

$timer1 = microtime(true) - $timer;

$timer = microtime(true);
$injector = unserialize($serialize);
foreach (range(1, $n) as $i) {
    $injector->getInstance(FakeCarInterface::class);
}
$timer2 = microtime(true) - $timer;

$compiler = new DiCompiler(new FakeCarModule, $_ENV['TMP_DIR']);
$compiler->compile();
$timer = microtime(true);
$injector = new ScriptInjector($_ENV['TMP_DIR']);
foreach (range(1, $n) as $i) {
    $injector->getInstance(FakeCarInterface::class);
}
$timer3 = microtime(true) - $timer;

// Microsecond per inject
printf("micro second per inject (MPI):%f speed:x%d\n", $timer2 / $n, $timer1 / $timer2);
printf("micro second per inject (MPI):%f speed:x%d\n", $timer3 / $n, $timer1 / $timer3);
