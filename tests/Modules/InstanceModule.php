<?php

namespace Aura\Di\Modules;

use Aura\Di\AbstractModule;

class InstanceModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('id')->toInstance('PC6001');
        $this->bind()->annotatedWith('user_name')->toInstance('koriym');
        $this->bind()->annotatedWith('user_age')->toInstance(21);
        $this->bind()->annotatedWith('user_gender')->toInstance('male');
        $this->bind('Aura\Di\Mock\DbInterface')->to('\Aura\Di\Mock\UserDb');
        $this->bind('Aura\Di\Mock\UserInterface')->toInstance(new \Aura\Di\Mock\User);
    }
}