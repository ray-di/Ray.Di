<?php

namespace Ray\Di\Modules;

use Ray\Di\Aop\TaxCharger;
use Ray\Di\AbstractModule;

use Ray\Aop\Matcher;
use Doctrine\Common\Annotations\AnnotationReader as Reader;

class AopMatcherModule extends AbstractModule
{
    protected function configure()
    {
        $matcher = new Matcher(new Reader);
        $this->bindInterceptor(
            $matcher->subclassesOf('Ray\Di\Aop\RealBillingService'),
            $matcher->any(),
            [new TaxCharger]
        );
    }
}
