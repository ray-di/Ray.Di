<?php

namespace Ray\zftest;

use Ray\Di\AbstractModule;

class TestModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\Mock\DbInterface')->to('Ray\Di\Mock\UserDb');
    }
}