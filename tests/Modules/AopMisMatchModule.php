<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\SalesTax;

class AopMisMatcher extends AbstractModule
{
    protected function configure()
    {
        $classMatcher = function($class) {
            return false;
        };
        $methodMatcher = function($method) {return true;};
        $this->bindInterceptor($classMatcher, $methodMatcher, [new SalesTax]);
    }
}
