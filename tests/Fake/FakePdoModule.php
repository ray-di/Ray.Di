<?php
namespace Ray\Di;

class FakePdoModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(\PDO::class)->in(Scope::SINGLETON);
        $this->bind(\PDO::class)->toConstructor(\PDO::class, 'dsn=pdo_dsn');
        $this->bind()->annotatedWith('pdo_dsn')->toInstance('sqlite::memory:');
    }
}
