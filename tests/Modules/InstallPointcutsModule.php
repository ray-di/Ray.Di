<?php


namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;
use Ray\Di\Tests\ChildTaxCharger;
use Ray\Di\Modules\AopModule;

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
    }
}
