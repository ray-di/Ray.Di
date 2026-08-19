<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\CircularDependency;
use Ray\Di\Exception\ConcurrentSingletonConstruction;
use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Throwable;

use function assert;
use function reset;
use function Swoole\Coroutine\run;

/**
 * Coroutine safety of the shared container
 *
 * A container shared across Swoole coroutines must not report a false
 * CircularDependency when one coroutine suspends mid-resolution. Concurrent
 * construction of an unbuilt singleton is refused with
 * ConcurrentSingletonConstruction; warm up singletons before serving
 * requests so that request-time resolution only reads the cached instance.
 */
#[RequiresPhpExtension('swoole')]
class CoroutineSafetyTest extends TestCase
{
    protected function setUp(): void
    {
        FakeSlowProvider::$buildCount = 0;
    }

    public function testConcurrentTransientResolutionIsNotACycle(): void
    {
        $injector = new Injector(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeSlowResource::class)->toProvider(FakeSlowProvider::class);
            }
        });

        [$results, $errors] = $this->resolveConcurrently($injector);

        $this->assertCount(0, $errors, (string) ($errors[0] ?? ''));
        $this->assertCount(2, $results);
        $this->assertNotSame($results[0], $results[1]);
        $this->assertSame(2, FakeSlowProvider::$buildCount);
    }

    public function testConcurrentSingletonConstructionIsRefused(): void
    {
        $injector = new Injector(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeSlowResource::class)->toProvider(FakeSlowProvider::class)->in(Scope::SINGLETON);
            }
        });

        [$results, $errors] = $this->resolveConcurrently($injector);

        // one coroutine builds; the other is refused instead of building a duplicate
        $this->assertCount(1, $results);
        $this->assertCount(1, $errors);
        $this->assertInstanceOf(ConcurrentSingletonConstruction::class, reset($errors));
        $this->assertSame(1, FakeSlowProvider::$buildCount);
    }

    public function testWarmedUpSingletonIsSharedAcrossCoroutines(): void
    {
        $injector = new Injector(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeSlowResource::class)->toProvider(FakeSlowProvider::class)->in(Scope::SINGLETON);
            }
        });
        $warmed = $injector->getInstance(FakeSlowResource::class);

        [$results, $errors] = $this->resolveConcurrently($injector);

        $this->assertCount(0, $errors, (string) ($errors[0] ?? ''));
        $this->assertSame($warmed, $results[0]);
        $this->assertSame($warmed, $results[1]);
        $this->assertSame(1, FakeSlowProvider::$buildCount);
    }

    public function testRealCircularDependencyIsStillDetectedInsideCoroutine(): void
    {
        $injector = new Injector(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeCircularAInterface::class)->to(FakeCircularA::class);
                $this->bind(FakeCircularBInterface::class)->to(FakeCircularB::class);
            }
        });

        $caught = null;
        run(static function () use ($injector, &$caught): void {
            Coroutine::create(static function () use ($injector, &$caught): void {
                try {
                    $injector->getInstance(FakeCircularAInterface::class);
                } catch (CircularDependency $e) {
                    $caught = $e;
                }
            });
        });

        $this->assertInstanceOf(CircularDependency::class, $caught);
    }

    public function testResolutionSucceedsAfterConcurrentResolution(): void
    {
        $injector = new Injector(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeSlowResource::class)->toProvider(FakeSlowProvider::class)->in(Scope::SINGLETON);
                $this->bind(FakeDiamondSharedInterface::class)->to(FakeDiamondShared::class);
            }
        });

        $this->resolveConcurrently($injector);

        // resolution state must not leak across coroutines
        $this->assertInstanceOf(FakeDiamondShared::class, $injector->getInstance(FakeDiamondSharedInterface::class));
        $singleton = $injector->getInstance(FakeSlowResource::class);
        assert($singleton instanceof FakeSlowResource);
        $this->assertSame(1, FakeSlowProvider::$buildCount);
    }

    /**
     * Resolve FakeSlowResource in two coroutines whose provider suspends mid-construction
     *
     * @return array{array<int, FakeSlowResource>, array<int, Throwable>}
     */
    private function resolveConcurrently(Injector $injector): array
    {
        $results = [];
        $errors = [];
        run(static function () use ($injector, &$results, &$errors): void {
            $wg = new WaitGroup();
            for ($i = 0; $i < 2; $i++) {
                $wg->add();
                Coroutine::create(static function () use ($injector, &$results, &$errors, $wg, $i): void {
                    try {
                        $results[$i] = $injector->getInstance(FakeSlowResource::class);
                    } catch (Throwable $e) {
                        $errors[$i] = $e;
                    } finally {
                        $wg->done();
                    }
                });
            }

            $wg->wait();
        });

        return [$results, $errors];
    }
}
