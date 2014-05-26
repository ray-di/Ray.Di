<?php

use Ray\Di\Modules\SingletonRequestInjectionModule;
use Ray\Di\DiCompiler;
use Doctrine\Common\Cache\FilesystemCache;

require dirname(dirname(__DIR__)) . '/tests/bootstrap.php';

$moduleProvider = function () {return new SingletonRequestInjectionModule;};
$injector = DiCompiler::create($moduleProvider, new FilesystemCache($_ENV['TMP_DIR']), __METHOD__, $_ENV['TMP_DIR']);
$instance = $injector->getInstance('Ray\Di\Mock\SingletonInterceptorConsumer');
/** @var $instance \Ray\Di\Mock\SingletonInterceptorConsumer */
$db1 = $instance->getDb();

return $db1;
