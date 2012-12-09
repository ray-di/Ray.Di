<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Modules;

class InstallDoubleModule1 extends AbstractModule
{
    protected function configure()
    {
        $this->install(new \Ray\Di\Modules\Db1Module);
        $this->install(new \Ray\Di\Modules\Db2Module);
    }
}
