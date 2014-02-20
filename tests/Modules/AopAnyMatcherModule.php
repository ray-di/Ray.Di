<?php

namespace Ray\Di\Modules;

use Ray\Di\AbstractModule;

use Ray\Di\Aop\TaxCharger;

class AopAnyMatcherModule extends AbstractModule
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
