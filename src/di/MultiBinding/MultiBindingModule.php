<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use Ray\Di\AbstractModule;
use Ray\Di\ParameterAttributeReader;

class MultiBindingModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(ParameterAttributeReader::class);
        $this->bind(Map::class)->toProvider(MapProvider::class);
    }
}
