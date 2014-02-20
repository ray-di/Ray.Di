<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class StringProviderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('scalar_value')->toProvider('Ray\Di\Modules\StringProvider');
    }
}
