<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule,
    Ray\Di\Scope;

class ArrayInstance extends AbstractModule
{
    protected function configure()
    {
        $this->bind('')->annotatedWith('adapters')->toInstance(array('html' =>  new \StdClass, 'http' => new \StdClass));
    }
}
