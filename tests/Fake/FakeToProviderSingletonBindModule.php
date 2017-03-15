<?php
namespace Ray\Di;

class FakeToProviderSingletonBindModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeRobotInterface::class)->toProvider(FakeRobotProvider::class)->in(Scope::SINGLETON);
    }
}
