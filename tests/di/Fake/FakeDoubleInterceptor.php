<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class FakeDoubleInterceptor implements MethodInterceptor, FakeDoubleInterceptorInterface
{
    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();

        return $result * 2;
    }
}
