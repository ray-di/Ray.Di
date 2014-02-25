<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class RequestInjectionSingletonModule extends AbstractModule
{
    public $object;

    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->to('Ray\Di\Mock\UserDb')->in(Scope::SINGLETON);
        $this->object = $this->requestInjection('Ray\Di\Mock\DbInterface');
    }
}
