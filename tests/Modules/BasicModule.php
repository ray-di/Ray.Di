<?php

namespace Aura\Di\Modules;

use Aura\Di\AbstractModule;

class BasicModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Aura\Di\Mock\DbInterface')->to('Aura\Di\Mock\UserDb');
    }
}