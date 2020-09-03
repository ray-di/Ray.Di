<?php
namespace Ray\Di;

class FakeEngineToProviderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeEngine::class)->toProvider(FakeEngineProvider::class);
    }
}
