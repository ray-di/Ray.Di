<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class InvalidBindingModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\MovieApp\Lister')->toInstance(1);
    }
}
