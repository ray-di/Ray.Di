<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class InvalidProviderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->toProvider('Ray\Di\Modules\XXX');
    }
}
