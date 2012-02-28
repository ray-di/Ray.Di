<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

class NoAnnotationBindingModule extends AbstractModule
{
    public function __construct(&$injector)
    {
        $this->injector = $injector;
        $this->configure();
    }

    protected function configure()
    {
        $this->injector->params['Ray\Di\Mock\MovieApp\Lister'] = ['finder' => new \Ray\Di\Mock\MovieApp\Finder];
    }
}