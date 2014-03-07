<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class SingletonProviderForClassModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\RndDb')->toProvider('Ray\Di\Mock\RndDbProvider')->in(Scope::SINGLETON);
    }
}
