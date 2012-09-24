<?php

namespace Ray\Di\Tests;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class TaxCharger implements MethodInterceptor
{
    const defaultTaxRate = 0.05;

    public function invoke(MethodInvocation $invocation)
    {
        list($amount, $unit) =  $invocation->proceed();
        $annotation = $invocation->getAnnotation();
        $tax = $annotation ? $annotation->value : self::defaultTaxRate;
        $amount *= (1 + $tax);

        return [$amount, $unit];
    }
}
