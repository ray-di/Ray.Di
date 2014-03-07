<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Aop\Compiler;
use Ray\Aop\Bind;
use PHPParser_PrettyPrinter_Default;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
AnnotationRegistry::registerLoader([$loader, "loadClass"]);

return new Injector(
    new Container(
        new Forge(
            new Config(
                new Annotation(
                    new Definition,
                    new AnnotationReader)))),
    new EmptyModule,
    new Bind,
    new Compiler(
        sys_get_temp_dir(),
        new PHPParser_PrettyPrinter_Default
    ),
    new Logger
);
