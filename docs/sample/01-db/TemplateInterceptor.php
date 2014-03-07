<?php

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

/**
 * Template interceptor
 */
class TemplateInterceptor implements MethodInterceptor
{
    /**
     * @param MethodInvocation $invocation
     *
     * @return mixed|object|string
     */
    public function invoke(MethodInvocation $invocation)
    {
        $view = '';
        $result = $invocation->proceed();
        if (! is_array($result)) {
            return $result;
        }
        foreach ($result as &$row) {
            $view .= "Name:{$row['Name']}\tAge:{$row['Age']}\n";
        }
        echo $view;

        return $view;
    }
}
