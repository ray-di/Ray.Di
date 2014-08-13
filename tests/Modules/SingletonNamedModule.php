<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class SingletonNamedModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->annotatedWith('db')->to('Ray\Di\Mock\RndDb')->in(Scope::SINGLETON);
    }
}
