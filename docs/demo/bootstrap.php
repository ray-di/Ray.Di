<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
/* @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('Ray\Di\Demo\\', __DIR__ . '/src/');
// annotation loader
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
