<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class ProvideNotExistsModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->toProvider('NotExists');
    }
}
