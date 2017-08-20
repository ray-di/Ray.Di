<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/* @var $loader \Composer\Autoload\ClassLoader */
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);
$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
