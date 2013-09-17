<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Modules\BasicModule;
use Ray\Di\Scope;

class UseBasicModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new BasicModule);
        $this->bind('Ray\Di\Definition\BasicInterface')->to('Ray\Di\Definition\Basic')->in(Scope::SINGLETON);
    }
}
