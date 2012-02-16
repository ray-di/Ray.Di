<?php

$system = dirname(dirname(dirname(__DIR__)));
require_once $system . '/vendor/Doctrine.Common/lib/Doctrine/Common/ClassLoader.php';
$commonLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', $system . '/vendor/Doctrine.Common/lib');
$commonLoader->register();

$injector = require dirname(dirname(dirname(__DIR__))) . '/scripts/instance.php';
return $injector;