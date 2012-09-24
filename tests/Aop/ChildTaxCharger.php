<?php

namespace Ray\Di\Tests;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class ChildTaxCharger extends TaxCharger
{
    const defaultTaxRate = 0.08;
}
