<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Ray\Di\AbstractModule;
use Ray\Di\Injector;

// 	public PDO::__construct ( string $dsn [, string $username [, string $password [, array $options ]]] )

class PdoModuleLegacyNaming extends AbstractModule
{
    protected function configure()
    {
        // 'dsn=pdo_dsn' (still) works
        $this->bind(PDO::class)->toConstructor(\PDO::class, 'dsn=pdo_dsn');
        $this->bind()->annotatedWith('pdo_dsn')->toInstance('sqlite::memory:');
    }
}

class PdoModule extends AbstractModule
{
    protected function configure()
    {
        // ['dsn' => 'pdo_dsn'] works (recommended)
        $this->bind(PDO::class)->toConstructor(\PDO::class, ['dsn' => 'pdo_dsn']);
        $this->bind()->annotatedWith('pdo_dsn')->toInstance('sqlite::memory:');
    }
}

$injector = new Injector(new PdoModuleLegacyNaming);
$pdo = $injector->getInstance(PDO::class);
$works = $pdo instanceof PDO;

$injector = new Injector(new PdoModule);
$pdo = $injector->getInstance(PDO::class);
$works &= $pdo instanceof PDO;

echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
