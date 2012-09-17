<?php

namespace Ray\Di\Modules;

use Ray\Di\Tests\TaxCharger,
    Ray\Di\AbstractModule,
    Ray\Di\Scope,
    Ray\Di\SalesTax;

use Ray\Aop\Matcher;
use Doctrine\Common\Annotations\AnnotationReader as Reader;

/**
 * @deprecated
 *
 * not to set \Closure for serialize.
 */
class AopMatcherModule extends AbstractModule
{
    protected function configure()
    {
        $matcher = new Matcher(new Reader);
        $this->bindInterceptor(
            $matcher->subclassesOf('Ray\Di\Tests\RealBillingService'),
            $matcher->any(),
            [new TaxCharger]
        );
    }
}
