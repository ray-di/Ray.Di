<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class RequestInjectionModule extends AbstractModule
{
    public $object;

    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->to('Ray\Di\Mock\UserDb');
        $this->object = $this->requestInjection('Ray\Di\Definition\Basic');
    }
}
