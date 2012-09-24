<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class ArrayInstance extends AbstractModule
{
    protected function configure()
    {
        $this->bind('')->annotatedWith('adapters')->toInstance(['html' =>  new \StdClass, 'http' => new \StdClass]);
    }
}
