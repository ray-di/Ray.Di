<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;

class FakeNullObjectModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeTyreInterface::class)->toNull();
    }
}
