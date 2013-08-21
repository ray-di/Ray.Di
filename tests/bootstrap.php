<?php

error_reporting(E_ALL);

// Ensure that composer has installed all dependencies
if (!file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    die("Dependencies must be installed using composer:\n\n php composer.phar install --dev\n\n"
        . "See http://getcomposer.org for help with installing composer\n");
}

// vendor
require dirname(__DIR__) . '/vendor/autoload.php';
// library
require dirname(__DIR__) . '/src.php';
// tests
require __DIR__ . '/src.php';

$tmpDir = sys_get_temp_dir() . '/ray/';
if (! file_exists($tmpDir)) {
    @mkdir($tmpDir);
}
$rm = function ($dir) use (&$rm) {
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? $rm($file) : unlink($file);
        @rmdir($file);
    }
};
// clear cache folder
$rm($tmpDir);
$_ENV['RAY_TMP'] = $tmpDir;
