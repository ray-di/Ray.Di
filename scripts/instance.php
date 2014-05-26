<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache;
use Ray\Aop\Compiler;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
AnnotationRegistry::registerLoader([$loader, "loadClass"]);

$injector = (new InjectorFactory)->newInstance([new EmptyModule()], new ArrayCache, sys_get_temp_dir());

return $injector;
