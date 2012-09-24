<?php

namespace Ray\Di\Modules;

use Ray\Di\SalesTax;

use Ray\Di\AbstractModule;

class InterceptorModule extends AbstractModule
{
    protected function configure()
    {
        $this->registerInterceptAnnotation('SalasTax', [new SalesTax]);
    }
}
