<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeAopInterceptorModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->any(),
            [FakeDoubleInterceptor::class]
        );
    }
}
