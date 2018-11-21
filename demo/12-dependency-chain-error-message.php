<?php

declare(strict_types=1);

use Ray\Di\AbstractModule;
use Ray\Di\Exception\Unbound;
use Ray\Di\Injector;

require dirname(__DIR__) . '/vendor/autoload.php';

class A
{
    public function __construct(B $dep)
    {
    }
}

class B
{
    public function __construct(C $dep)
    {
    }
}

class C
{
    public function __construct(D $dep)
    {
    }
}

class D
{
    public function __construct(EInterface $dep)
    {
    }
}

interface EInterface
{
}

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

$injector = new Injector(new DeepLinkedClassBindingModule);
// this will fail with an exception as EInterface is not bound
try {
    $injector->getInstance(A::class);
} catch (Unbound $e) {
    echo $e->getMessage();
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
