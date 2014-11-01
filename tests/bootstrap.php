<?php

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('Ray\Di\\', dirname(__DIR__) . '/src');
$loader->addPsr4('Ray\Di\\', __DIR__ . '/Fake');
$loader->addPsr4('Ray\Di\\', __DIR__);

// annotation
require __DIR__ . '/Fake/FakeLeft.php';
require __DIR__ . '/Fake/FakeRight.php';

$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
