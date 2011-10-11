<?php

namespace Aura\Di\Modules;

use Aura\Di\AbstractModule;

class ProviderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Aura\Di\Mock\DbInterface')->toProvider('Aura\Di\Modules\DbProvider');
    }
}