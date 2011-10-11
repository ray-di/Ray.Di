<?php

namespace Aura\Di\Modules;

use Aura\Di\AbstractModule;

class ClosureModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Aura\Di\Mock\DbInterface')->toClosure(function(){return new \Aura\Di\Mock\UserDb;});
    }
}