<?php

use Ray\Di\Modules\AopModule;

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\SalesTax;
use Ray\Di\Tests\ChildTaxCharger;

class InstallPointcutsModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('Ray\Di\Tests\SalesTax'),
            [new ChildTaxCharger]
        );
        // @todo try this;
        $this->install(new AopModule($this));
//        $this->install(new AopModule);
    }
}
