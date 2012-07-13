<?php

error_reporting(E_ALL | E_STRICT);

// Ensure that composer has installed all dependencies
if (!file_exists(dirname(__DIR__) . '/composer.lock')) {
    die("Dependencies must be installed using composer:\n\nphp composer.phar install --dev\n\n"
        . "See http://getcomposer.org for help with installing composer\n");
}

// vendor
require dirname(__DIR__) . '/vendor/autoload.php';
// library
require dirname(__DIR__) . '/src.php';
// tests
require __DIR__ . '/src.php';
