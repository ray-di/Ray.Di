<?php

namespace Ray\Di;

require __DIR__ . '/bootstrap.php';
$n = 100;

$injector = new Injector(new FakeCarModule);
$serialize = serialize($injector);

$timer = microtime(true);
foreach (range(1, $n) as $i) {
    $injector = new Injector(new FakeCarModule);
    $injector->getInstance(FakeCarInterface::class);
}

$timer1 = microtime(true) - $timer;

$timer = microtime(true);
foreach (range(1, $n) as $i) {
    $injector = unserialize($serialize);
    $injector->getInstance(FakeCarInterface::class);
}
$timer2 = microtime(true) - $timer;

printf("%f msec per inject\n", $timer2 / $n * 1000);
printf("%f times faster in runtime\n", $timer1 / $timer2);
printf("# Files: %d\n",  count(get_included_files()));
printf("Memory usage: %d kb\n", memory_get_peak_usage()/1024);
