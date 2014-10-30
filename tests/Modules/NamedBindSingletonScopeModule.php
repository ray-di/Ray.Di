<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class NamedBindSingletonScopeModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->annotatedWith('first')->to('Ray\Di\Mock\Db')->in(Scope::SINGLETON);
        $this->bind('Ray\Di\Mock\DbInterface')->annotatedWith('second')->to('Ray\Di\Mock\Db')->in(Scope::SINGLETON);
    }
}
