<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\EnvNotFound;

use function putenv;

class EnvProviderTest extends TestCase
{
    public const KEY = 'RAY_DI_ENV_PROVIDER_TEST';

    protected function tearDown(): void
    {
        putenv(self::KEY);
    }

    public function testGetReturnsEnvValue(): void
    {
        putenv(self::KEY . '=first');

        $this->assertSame('first', $this->getInjector()->getInstance('', 'x'));
    }

    public function testGetReadsEnvOnEachCall(): void
    {
        $injector = $this->getInjector();
        putenv(self::KEY . '=first');
        $this->assertSame('first', $injector->getInstance('', 'x'));

        putenv(self::KEY . '=second');
        $this->assertSame('second', $injector->getInstance('', 'x'));
    }

    public function testUnsetEnvThrowsEnvNotFound(): void
    {
        $this->expectException(EnvNotFound::class);
        $this->expectExceptionMessage(self::KEY);

        $this->getInjector()->getInstance('', 'x');
    }

    private function getInjector(): Injector
    {
        return new Injector(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind()->annotatedWith('x')->toProvider(EnvProvider::class, EnvProviderTest::KEY);
            }
        });
    }
}
