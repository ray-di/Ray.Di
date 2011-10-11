<?php

namespace Aura\Di\Modules;

use Aura\Di\AbstractModule,
    Aura\Di\Scope;

class PrototypeModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Aura\Di\Mock\DbInterface')->to('Aura\Di\Mock\RndDb')->in(Scope::PROTOTYPE);
    }
}