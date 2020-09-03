<?php
namespace Ray\Di;

class FakeAopInterfaceModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeAopInterface::class)->to(FakeAop::class);
    }
}
