<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Set;

use const PHP_VERSION_ID;

class MultiBindingModule extends AbstractModule
{
    protected function configure(): void
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->bind(Map::class)->annotatedWith(Set::class)->toProvider(MapProvider::class);
        }
    }
}
