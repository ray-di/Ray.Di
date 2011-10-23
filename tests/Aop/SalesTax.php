<?php

namespace Ray\Di;

use Ray\Aop\MethodInterceptor,
    Ray\Aop\MethodInvocation;

class SalesTax implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        list($amount, $unit) =  $invocation->proceed();
        $amount *= 1.05;
        return array($amount, $unit);
    }
}