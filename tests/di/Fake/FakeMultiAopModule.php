<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeMultiAopModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(FakeMultiAopInterface::class)->to(FakeMultiAop::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->startsWith('method'),
            [FakeDoubleInterceptor::class]
        );
    }
}
