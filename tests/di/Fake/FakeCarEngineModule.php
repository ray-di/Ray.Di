<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeCarEngineModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeEngine::class)->to(FakeCarEngine::class);
    }
}
