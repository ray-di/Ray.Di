<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule,
    Ray\Di\Scope,
    Ray\Di\SalesTax;

class AopModule extends AbstractModule
{
    protected function configure()
    {
        $this->registerInterceptAnnotation('SalesTax', array(new SalesTax));
    }
}