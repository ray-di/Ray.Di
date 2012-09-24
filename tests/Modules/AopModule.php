<?php

namespace Ray\Di\Modules;

use Ray\Di\Tests\TaxCharger;

use Ray\Di\AbstractModule;
use Ray\Aop\Matcher;

class AopModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->any(), [
                new TaxCharger
            ]
        );
    }
}
