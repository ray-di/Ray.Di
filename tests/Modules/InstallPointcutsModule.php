<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Aop\ChildTaxCharger;

class InstallPointcutsModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('Ray\Di\Aop\SalesTax'),
            [new ChildTaxCharger]
        );
        $this->install(new AopModule($this));
    }
}
