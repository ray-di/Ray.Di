<?php

namespace Aura\Di\Modules;

use Aura\Di\AbstractModule;

class MultiModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Aura\Di\Mock\DbInterface')->to('Aura\Di\Mock\UserDb');
        $this->bind('Aura\Di\Mock\DbInterface')->annotatedWith('user_db')->to('Aura\Di\Mock\UserDb');
    }
}