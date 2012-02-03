<?php

namespace Ray\Di\Modules;

use Ray\Di\TaxCharger;

use Ray\Di\AbstractModule,
Ray\Di\Scope,
Ray\Di\SalesTax;

class AopMatcherModule extends AbstractModule
{
    protected function configure()
    {
        $classMatcher = function($class) {
            if ($class === 'Ray\Di\RealBillingService') {
                return true;
            }
        };
        $methodMatcher = function($method) {return true;};
        $this->bindInterceptor($classMatcher, $methodMatcher, array(new TaxCharger));
    }
}