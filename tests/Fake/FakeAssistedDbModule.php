<?php
namespace Ray\Di;

class FakeAssistedDbModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeAbstractDb::class)->toProvider(FakeAssistedDbProvider::class);
    }
}
