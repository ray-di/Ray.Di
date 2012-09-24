<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class AnnotateModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->annotatedWith('user_db')->to('Ray\Di\Mock\UserDb')->in(Scope::SINGLETON);
    }
}
