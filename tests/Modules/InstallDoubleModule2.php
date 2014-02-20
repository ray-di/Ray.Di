<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Modules;

class InstallDoubleModule2 extends AbstractModule
{
    protected function configure()
    {
        $this->install(new Db1Module);
        $this->install(new Db2Module($this));
    }
}
