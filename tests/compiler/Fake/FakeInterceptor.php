<?php

namespace Ray\Compiler;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class FakeInterceptor implements MethodInterceptor
{
    public static $args;
    public function invoke(MethodInvocation $invocation)
    {
        self::$args = $invocation->getArguments();

        return $invocation->proceed();
    }
}
