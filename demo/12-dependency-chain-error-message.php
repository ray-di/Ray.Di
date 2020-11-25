<?php

declare(strict_types=1);

use Composer\Autoload\ClassLoader;
use Ray\Di\AbstractModule;
use Ray\Di\Exception\Unbound;
use Ray\Di\Injector;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var ClassLoader $loader */
$loader->addPsr4('', __DIR__ . '/chain-error');

class DeepLinkedClassBindingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(A::class);
        $this->bind(B::class);
        $this->bind(C::class);
        $this->bind(D::class);
        // purposefully not bound.
        // D will require EInterface object to be injected, but
        // EInterface is not bound and an Unbound exception is thrown.
    }
}

$injector = new Injector(new DeepLinkedClassBindingModule());
// this will fail with an exception as EInterface is not bound

try {
    $injector->getInstance(A::class);
} catch (Unbound $e) {
    $msg = $e->getMessage();
    ob_start();
    // dependency 'B' with name '' used
    //    dependency 'B' with name '' used in12-dependency-chain-error-message.php:11
    echo PHP_EOL . '---------' . PHP_EOL;
    echo $e;
//    exception 'Ray\Di\Exception\Unbound' with message 'EInterface-'
//    - dependency 'EInterface' with name '' used in12-dependency-chain-error-message.php:26
//    - dependency 'D' with name '' used in12-dependency-chain-error-message.php:21
//    - dependency 'C' with name '' used in12-dependency-chain-error-message.php:16
//    - dependency 'B' with name '' used in12-dependency-chain-error-message.php:11
    // ...
    echo PHP_EOL . '---------' . PHP_EOL;
    do {
        echo get_class($e) . ':' . $e->getMessage() . PHP_EOL;
    } while ($e = $e->getPrevious());

//    Ray\Di\Exception\Unbound:dependency 'B' with name '' used in12-dependency-chain-error-message.php:11
//    Ray\Di\Exception\Unbound:dependency 'C' with name '' used in12-dependency-chain-error-message.php:16
//    Ray\Di\Exception\Unbound:dependency 'D' with name '' used in12-dependency-chain-error-message.php:21
//    Ray\Di\Exception\Unbound:dependency 'EInterface' with name '' used in12-dependency-chain-error-message.php:26
//    Ray\Di\Exception\Unbound:EInterface-
}

$log = ob_get_clean();
$works = strpos($log, "dependency 'B' with name '' used");
echo $works ? 'It works!' : 'It DOES NOT work!';
file_put_contents(__DIR__ . '/error.log', $log);

echo '[error log]: ' . __DIR__ . '/error.log' . PHP_EOL;
