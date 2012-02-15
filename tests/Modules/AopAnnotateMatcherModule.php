<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule,
    Ray\Di\Scope,
    Ray\Di\Matcher;

use Ray\Di\SalesTax,
    Ray\Di\Tests\TaxCharger;

class AopAnnotateMatcherModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor($this->matcher->any(), $this->matcher->annotatedWith('Ray\Di\Tests\SalesTax'), [new TaxCharger]);
    }
}