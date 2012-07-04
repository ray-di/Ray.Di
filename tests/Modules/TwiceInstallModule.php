<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Modules;

class TwiceInstallModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new Modules\PassDependencyModule(1));
        $this->install(new Modules\PassDependencyModule(2));
    }
}
