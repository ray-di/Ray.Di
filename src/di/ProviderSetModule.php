<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Set;

class ProviderSetModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(ProviderInterface::class)->annotatedWith(Set::class)->toProvider(ProviderSetProvider::class);
    }
}
