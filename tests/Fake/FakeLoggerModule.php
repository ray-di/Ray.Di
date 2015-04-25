<?php

namespace Ray\Di;

class FakeLoggerModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeLoggerInterface::class)->toProvider(FakeLoggerPointProvider::class);
        $this->bind(FakeLoggerConsumer::class);
    }
}
