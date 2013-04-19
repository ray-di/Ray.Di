<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class InstanceModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('id')->toInstance('PC6001');
        $this->bind()->annotatedWith('user_name')->toInstance('koriym');
        $this->bind()->annotatedWith('user_age')->toInstance(21);
        $this->bind()->annotatedWith('user_gender')->toInstance('male');
        $this->bind('Ray\Di\Mock\DbInterface')->to('\Ray\Di\Mock\UserDb');
        $this->bind('Ray\Di\Mock\UserInterface')->toInstance(new \Ray\Di\Mock\User);
    }
}
