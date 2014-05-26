<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class SingletonRequestInjectionModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new SingletonModule);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->startsWith('get'),
            [$this->requestInjection('Ray\Di\Mock\SingletonInterceptor')]
        );
    }
}
