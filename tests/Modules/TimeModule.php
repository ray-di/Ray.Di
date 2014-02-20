<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class TimeModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('now')->toInstance(time());
    }
}
