<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class ConstructorModule extends AbstractModule
{
    protected function configure()
    {
        $this
            ->bind('Ray\Di\Mock\AbstractClassWithConstructor')
            ->toProvider('\Ray\Di\Mock\ConcreteClassWithoutConstructorProvider');
    }

}
