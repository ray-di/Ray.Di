<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class SingletonProviderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->toProvider('Ray\Di\Mock\RndDbProvider')->in(Scope::SINGLETON);
    }
}
