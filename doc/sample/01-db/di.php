<?php

$system = dirname(dirname(dirname(__DIR__)));
require_once $system . '/vendor/autoload.php';

$injector = require dirname(dirname(dirname(__DIR__))) . '/scripts/instance.php';
return $injector;
