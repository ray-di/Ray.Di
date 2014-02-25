<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Mock\UserDb;

class ClosureModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->toCallable(function(){return new UserDb;});
    }
}
