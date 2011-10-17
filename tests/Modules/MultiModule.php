<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class MultiModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->to('Ray\Di\Mock\UserDb');
        $this->bind('Ray\Di\Mock\DbInterface')->annotatedWith('user_db')->to('Ray\Di\Mock\UserDb');
    }
}