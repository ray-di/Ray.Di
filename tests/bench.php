<?php

namespace Ray\Di;

require __DIR__ . '/bootstrap.php';
$n = 1000;

$injector = new Injector(new FakeCarModule);
$serialize = serialize($injector);

$timer = microtime(true);
foreach (range(1, $n) as $i) {
    $injector = new Injector(new FakeCarModule);
    $injector->getInstance(FakeCarInterface::class);
}

$timer1 = microtime(true) - $timer;
var_dump($timer1);

$timer = microtime(true);
foreach (range(1, $n) as $i) {
    $injector = unserialize($serialize);
    $injector->getInstance(FakeCarInterface::class);
}
$timer2 = microtime(true) - $timer;
var_dump($timer2);
var_dump($timer1 / $timer2);

var_dump(memory_get_peak_usage(true));
