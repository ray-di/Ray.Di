<?php

$loader = require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->add('', __DIR__);
$loader->register();
