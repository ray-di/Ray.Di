<?php

use Ray\Di\Di\PreDestroy;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
$loader->set('Ray', dirname(__DIR__));

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

class PreDestroyBox
{
    /**
     * @PreDestroy
     */
    public function onShutDown()
    {
        echo "shutdown" . PHP_EOL;
    }

    public function say()
    {
        echo "hello" . PHP_EOL;
    }
}

$module = function(){ return new \Ray\Di\EmptyModule;};
$injector = new \Ray\Di\CacheInjector($module, __DIR__ . '/tmp', new FilesystemCache(__DIR__ . '/tmp'));
$a = $injector->getInstance('PreDestroyBox');
echo "start" . PHP_EOL;
$a->say();
