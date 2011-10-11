<?php

namespace Aura\Di\Modules;

use Aura\Di\AbstractModule,
    Aura\Di\Scope;

class AnnotateModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Aura\Di\Mock\DbInterface')->annotatedWith('user_db')->to('Aura\Di\Mock\UserDb')->in(Scope::SINGLETON);
    }
}