<?php

namespace Aura\Di\Modules;

use Aura\Di\AbstractModule,
    Aura\Di\Scope;

class InvalidAnnotateModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Aura\Di\Mock\NoInterface')->annotatedWith('user_db')->to('Aura\Di\Mock\UserDb')->in(Scope::SINGLETON);
    }
}