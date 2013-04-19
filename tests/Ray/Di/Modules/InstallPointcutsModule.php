<?php


namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Aop\ChildTaxCharger;
use Ray\Di\Modules\AopModule;

class InstallPointcutsModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('Ray\Di\Aop\SalesTax'),
            [new ChildTaxCharger]
        );
        // @todo try this;
        $this->install(new AopModule($this));
    }
}
