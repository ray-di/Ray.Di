<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeMultiInterceptorModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeAop::class);
        // two interceptors bound in a SINGLE bindInterceptor() call
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->any(),
            [FakeDoubleInterceptor::class, FakeIncrementInterceptor::class]
        );
    }
}
