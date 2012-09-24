<?php

namespace Ray\Di\Tests;

use Ray\Aop\MethodInterceptor,
    Ray\Aop\MethodInvocation;

class ChildTaxCharger extends TaxCharger
{
    const defaultTaxRate = 0.08;
}
