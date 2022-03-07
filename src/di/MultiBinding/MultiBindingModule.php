<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use Ray\Di\AbstractModule;
use Ray\Di\ConstractorParamDualReader;

class MultiBindingModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(ConstractorParamDualReader::class);
        $this->bind(Map::class)->toProvider(MapProvider::class);
    }
}
