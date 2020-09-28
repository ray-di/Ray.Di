<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeAssistedDbModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeAbstractDb::class)->toProvider(FakeAssistedDbProvider::class);
    }
}
