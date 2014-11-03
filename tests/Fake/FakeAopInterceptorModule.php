<?php

namespace Ray\Di;

class FakeAopInterceptorModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptors(
            $this->matcher->any(),
            $this->matcher->any(),
            [FakeDoubleInterceptor::class]
        );
    }
}
