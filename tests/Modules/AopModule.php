<?php

namespace Ray\Di\Modules;

use Ray\Di\Aop\TaxCharger;
use Ray\Di\AbstractModule;

class AopModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->any(),
            [new TaxCharger]
        );
    }
}
