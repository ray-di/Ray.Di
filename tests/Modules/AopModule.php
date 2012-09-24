<?php

namespace Ray\Di\Modules;

use Ray\Di\Tests\TaxCharger;

use Ray\Di\AbstractModule,
    Ray\Aop\Matcher,
    Ray\Di\Scope,
    Ray\Di\SalesTax;
use Doctrine\Common\Annotations\AnnotationReader;


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
