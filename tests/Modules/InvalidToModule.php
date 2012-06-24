<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class InvalidToModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->to('XXX');
    }
}
