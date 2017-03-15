<?php
namespace Ray\Di;

class FakePriorityModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->any(),
            [FakeDoubleInterceptor::class]
        );

        $this->bindPriorityInterceptor(
            $this->matcher->any(),
            $this->matcher->any(),
            [FakeDoubleInterceptor::class]
        );
    }
}
