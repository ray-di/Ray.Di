<?php
namespace Ray\Di;

$systemPath = dirname(dirname(dirname(__DIR__)));
require $systemPath . '/src.php';
require $systemPath . '/vendor/Ray.Aop/src.php';

require $systemPath . '/vendor/Doctrine.Common/lib/Doctrine/Common/ClassLoader.php';
$commonLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', $systemPath . '/vendor/Doctrine.Common/lib');
$commonLoader->register();

$injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition)))), new EmptyModule);
return $injector;