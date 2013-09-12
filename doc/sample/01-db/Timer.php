<?php

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

/**
 * Timer interceptor
 */
class Timer implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Timer start\n";
        $mTime = microtime(true);
        $invocation->proceed();
        $time = microtime(true) - $mTime;
        echo "Timer stop:[" . sprintf('%01.7f', $time) . "] sec\n\n";
    }
}
