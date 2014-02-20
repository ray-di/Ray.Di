<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class MultiModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->to('Ray\Di\Mock\UserDb');
        $this->bind('Ray\Di\Mock\DbInterface')->annotatedWith('user_db')->to('Ray\Di\Mock\UserDb');
        $this->bind('Ray\Di\Mock\DbInterface')->annotatedWith('admin_db')->to('Ray\Di\Mock\AdminDb');
        $this->bind('Ray\Di\Mock\DbInterface')->annotatedWith('production_db')->to('Ray\Di\Mock\RndDb');
        $this->bind('Ray\Di\Mock\UserInterface')->annotatedWith('admin_user')->to('Ray\Di\Mock\User');
    }
}
