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
            sys_get_temp_dir(),
            new PHPParser_PrettyPrinter_Default,
            new PHPParser_Parser(new PHPParser_Lexer),
            new PHPParser_BuilderFactory
        )
    );
};
$postInject = function($instance) {};
$injector = new CacheInjector($injector, $postInject, 'test', new FilesystemCache(__DIR__ . '/object_files'), $_ENV['RAY_TMP']);
$billing = $injector->getInstance('Ray\Di\Aop\CacheBilling');

return serialize($billing);
