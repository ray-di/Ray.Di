<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Modules;

class DoubleBindModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->to('Ray\Di\Mock\UserDb1');
        $this->bind('Ray\Di\Mock\DbInterface')->to('Ray\Di\Mock\UserDb2');
//        $this->install(new Modules\BasicModule);
    }
}
