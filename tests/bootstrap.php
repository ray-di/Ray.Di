<?php

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('Ray\Di\\', dirname(__DIR__) . '/src');
$loader->addPsr4('Ray\Di\\', __DIR__ . '/Fake');
$loader->addPsr4('Ray\Di\\', __DIR__);
