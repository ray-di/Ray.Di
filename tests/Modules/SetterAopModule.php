<?php

namespace Ray\Di\Modules;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\AbstractModule;

class NoSetterInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        return null;
    }
}

class SetterAopModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new BasicModule);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->any(),
            [$this->requestInjection(__NAMESPACE__ . '\NoSetterInterceptor')]
        );
    }
}
