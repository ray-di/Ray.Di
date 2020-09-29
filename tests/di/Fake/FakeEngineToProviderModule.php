<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeEngineToProviderModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeEngine::class)->toProvider(FakeEngineProvider::class);
    }
}
