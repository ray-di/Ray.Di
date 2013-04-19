<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;

class NoAnnotationBindingModule extends AbstractModule
{
    /**
     * @var \Ray\Di\InjectorInterface
     */
    public $injector;

    public function __construct(InjectorInterface $injector)
    {
        $this->injector = $injector;
        $this->configure();
    }

    protected function configure()
    {
        $this->bind('Ray\Di\Mock\MovieApp\Lister')->toConstructor(['finder' => new \Ray\Di\Mock\MovieApp\Finder]);
    }
}
