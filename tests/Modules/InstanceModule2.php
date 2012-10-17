<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class InstanceModule2 extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('id')->toInstance(true);
        $this->bind('Ray\Di\Mock\DbInterface')->toProvider('Ray\Di\Modules\DbProvider');
    }
}
