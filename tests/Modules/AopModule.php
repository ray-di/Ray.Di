<?php

namespace Ray\Di\Modules;

use Ray\Di\Tests\TaxCharger;

use Ray\Di\AbstractModule,
    Ray\Di\Matcher,
    Ray\Di\Scope,
    Ray\Di\SalesTax;

class AopModule extends AbstractModule
{
    protected function configure()
    {
        $matcher = new Matcher;
        $this->bindInterceptor($matcher->any(), $matcher->any(), array(new TaxCharger()));
//         $this->registerInterceptAnnotation('SalesTax', array(new SalesTax));
    }
}
