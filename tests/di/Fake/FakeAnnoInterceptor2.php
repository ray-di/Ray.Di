<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class FakeAnnoInterceptor2 implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        FakeAnnoClass::$order[] = self::class;

        return $invocation->proceed();
    }
}
