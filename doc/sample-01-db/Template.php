<?php
namespace Ray\Di\Sample;

use Ray\Aop\MethodInterceptor,
Ray\Aop\MethodInvocation;

/**
 * Template interceptor
 */
class Template implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $view = '';
        $result = $invocation->proceed();
        foreach ($result as &$row) {
            $view .= "Name:{$row['Name']}\tAge:{$row['Age']}\n";
        }
        return $view;
    }
}