<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
AnnotationRegistry::registerLoader(array($loader, "loadClass"));

return new Injector(new Container(new Forge(new Config(new Annotation(new Definition)))));