<?php
namespace Ray\Di\Sample;

use Ray\Aop\MethodInterceptor,
Ray\Aop\MethodInvocation;

/**
 * Timer interceptor
 */
class Timer implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        echo "Timer start\n";
        $mtime = microtime(true);
        $invocation->proceed();
        $time = microtime(true) - $mtime;
        echo "Timer stop:[" . sprintf('%01.7f', $time) . "] sec\n\n";
    }
}
