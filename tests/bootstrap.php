<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use Doctrine\Common\Annotations\AnnotationRegistry;

$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
AnnotationRegistry::registerLoader('class_exists');
