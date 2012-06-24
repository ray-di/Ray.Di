<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class InstallModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new \Ray\Di\Modules\BasicModule);
        $this->bind('Ray\Di\Mock\LogInterface')->to('Ray\Di\Mock\Log');
    }
}
