<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
AnnotationRegistry::registerLoader([$loader, "loadClass"]);

return new Injector(new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader)))));