<?php
namespace Ray\Di;

class FakeCarEngineModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeEngine::class)->to(FakeCarEngine::class);
    }
}
