<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use Ray\Di\AbstractModule;
use Ray\Di\ParameterReader;

class MultiBindingModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(ParameterReader::class);
        $this->bind(Map::class)->toProvider(MapProvider::class);
    }
}
