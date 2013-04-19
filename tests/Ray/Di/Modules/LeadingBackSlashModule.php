<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class LeadingBackSlashModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('\Ray\Di\Mock\DbInterface')->to('\Ray\Di\Mock\UserDb');
    }
}
