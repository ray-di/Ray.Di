<?php

error_reporting(E_ALL);

use Doctrine\Common\Annotations\AnnotationRegistry;

// Ensure that composer has installed all dependencies
if (!file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    die("Dependencies must be installed using composer:\n\n php composer.phar install --dev\n\n"
        . "See http://getcomposer.org for help with installing composer\n");
}

ini_set('xdebug.max_nesting_level', 300);

// vendor
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('Ray\Di\\', __DIR__);
/** @var $loader \Composer\Autoload\ClassLoader */
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$rm = function ($dir) use (&$rm) {
    foreach (glob($dir . '/*') as $file) {
        is_dir($file) ? $rm($file) : unlink($file);
        @rmdir($file);
    }
};

$rm(__DIR__ . '/scripts/aop_files');
$rm(__DIR__ . '/scripts/object_files');
$rm(__DIR__ . '/scripts/cache');
$rm(__DIR__ . '/tmp');

$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
$_ENV['PACKAGE_DIR'] = dirname(__DIR__);
