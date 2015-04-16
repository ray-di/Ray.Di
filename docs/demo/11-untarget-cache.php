<?php

namespace Ray\Di\Demo;

use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\EmptyModule;
use Ray\Di\FakeRobot;
use Ray\Di\FakeRobotTeam;
use Ray\Di\Injector;

require __DIR__ . '/bootstrap.php';

// save file cache
$tmpDir = __DIR__ . '/tmp';
$cache = new FilesystemCache($tmpDir);
$cache->setNamespace('test-11');

$injector = $cache->fetch('injector');
if (! $injector) {
    $injector = new Injector(new EmptyModule, $tmpDir, $cache);
    $cache->save('injector', $injector);
    echo 'save, ';
} else {
    echo 'load, ';
}
$start = microtime(true);
/* @var $robotTeam1 FakeRobotTeam */
$robotTeam1 = $injector->getInstance(FakeRobotTeam::class);
$time = microtime(true) - $start;

$works = ($robotTeam1->robot1 instanceof FakeRobot) && ($robotTeam1->robot2 instanceof FakeRobot);
echo ($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
echo $time * 1000 . 'msec' . PHP_EOL;
