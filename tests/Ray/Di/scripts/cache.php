<?php

namespace Ray\Di;

use Doctrine\Common\Cache\FilesystemCache;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPParser_PrettyPrinter_Default;
use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_BuilderFactory;

require_once dirname(dirname(dirname(__DIR__))) . '/bootstrap.php';

$injector = function () {
    $container = new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader))));
    return new Injector(
        $container,
        new Modules\AopModule,
        new Bind,
        new Compiler(
            __DIR__ . '/aop_files',
            new PHPParser_PrettyPrinter_Default
        )
    );
};
$initialization = function($instance) {};
$injector = new CacheInjector($injector, $initialization, 'test', new FilesystemCache(__DIR__ . '/cache'));
$billing = $injector->getInstance('Ray\Di\Aop\CacheBilling');

return serialize($billing);
