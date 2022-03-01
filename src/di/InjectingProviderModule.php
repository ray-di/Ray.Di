<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Set;

class InjectingProviderModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(ProviderInterface::class)->annotatedWith(Set::class)->toProvider(InjectingProviderProvider::class);
    }
}
