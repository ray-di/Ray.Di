<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Injector;

class InjectorModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\InjectorInterface')->toInstance(Injector::create());
        $this->bind('Ray\Di\Mock\DbInterface')->to('Ray\Di\Mock\UserDb');
    }
}
