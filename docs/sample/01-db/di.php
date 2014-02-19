<?php

chdir(dirname(dirname(dirname(__DIR__))));

require_once 'vendor/autoload.php';

$injector = require 'scripts/instance.php';
return $injector;
