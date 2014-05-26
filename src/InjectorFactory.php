<?php

namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\Cache;
use PHPParser_PrettyPrinter_Default;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;

final class InjectorFactory
{
    /**
     * Return Injector instance
     * 
     * @param array $modules
     * @param Cache $cache
     * @param null  $tmpDir
     *
     * @return Injector
     */
    public function newInstance(array $modules = [], Cache $cache = null, $tmpDir = null)
    {
        $tmpDir ?: sys_get_temp_dir();
        $annotationReader = ($cache instanceof Cache) ? new CachedReader(new AnnotationReader, $cache) : new AnnotationReader;
        $injector = new Injector(
            new Container(new Forge(new Config(new Annotation(new Definition, $annotationReader)))),
            new EmptyModule,
            new Bind,
            new Compiler(
                $tmpDir,
                new PHPParser_PrettyPrinter_Default
            ),
            new Logger
        );

        if (count($modules) > 0) {
            $module = array_shift($modules);
            foreach ($modules as $extraModule) {
                /* @var $module AbstractModule */
                $module->install($extraModule);
            }
            $injector->setModule($module);
        }

        return $injector;
    }
}
