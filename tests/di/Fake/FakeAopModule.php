<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeAopModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeAopInterface::class)->to(FakeAop::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->any(),
            [FakeDoubleInterceptor::class]
        );
    }
}
