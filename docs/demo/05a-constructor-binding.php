<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
require __DIR__ . '/bootstrap.php';

use Ray\Di\AbstractModule;
use Ray\Di\Injector;

// 	public PDO::__construct ( string $dsn [, string $username [, string $password [, array $options ]]] )

class PdoModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(PDO::class)->toConstructor(\PDO::class, 'dsn=pdo_dsn');
        $this->bind()->annotatedWith('pdo_dsn')->toInstance('sqlite::memory:');
    }
}

$injector = new Injector(new PdoModule);
$pdo = $injector->getInstance(PDO::class);
$works = $pdo instanceof PDO;

echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
