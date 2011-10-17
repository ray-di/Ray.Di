<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class ClosureModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->toClosure(function(){return new \Ray\Di\Mock\UserDb;});
    }
}