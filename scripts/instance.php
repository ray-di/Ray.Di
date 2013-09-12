<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Aop\Compiler;
use Ray\Aop\Bind;
use PHPParser_PrettyPrinter_Default;
use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_BuilderFactory;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
AnnotationRegistry::registerLoader([$loader, "loadClass"]);

return new Injector(
    new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader)))),
        new EmptyModule,
        new Bind,
        new Compiler(
            sys_get_temp_dir(),
            new PHPParser_PrettyPrinter_Default,
            new PHPParser_Parser(new PHPParser_Lexer),
            new PHPParser_BuilderFactory
        ),
        new Logger
);
