<?php

namespace Ray\Di\Demo;

use Ray\Di\Injector;
use Ray\Di\AbstractModule;

require __DIR__ . '/bootstrap.php';

// returns first exception in exception trace
function helperGrabPrevExc($e)
{
    do {
        $current = $e;
    } while ($e = $e->getPrevious());
    return $current;
}

class DeepLinkedClassBindingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(A::class)->to(A::class);
        $this->bind(B::class)->to(B::class);
        $this->bind(C::class)->to(C::class);
        $this->bind(D::class)->to(D::class);
        // purposefully not bound.
        // D will require E to be injected, but
        // E is not bound and an Unbound exception is thrown.
        // $this->bind(E::class)->to(E::class);
    }
}




$injector = new Injector(new DeepLinkedClassBindingModule);
// this will fail with an exception as E is not bound
try {
    $injector->getInstance(A::class);
} catch (\Exception $e) {
    print helperGrabPrevExc($e)->getMessage();
}

