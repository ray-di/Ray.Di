<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Matcher;

use Ray\Di\SalesTax;
use Ray\Di\Tests\TaxCharger;

class AopAnnotateMatcherModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor($this->matcher->any(), $this->matcher->annotatedWith('Ray\Di\Tests\SalesTax'), [new TaxCharger]);
    }
}
