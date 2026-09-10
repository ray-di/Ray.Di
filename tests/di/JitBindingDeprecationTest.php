<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\Unbound;

use function array_slice;
use function count;
use function restore_error_handler;
use function set_error_handler;

use const E_USER_DEPRECATED;

/**
 * Just-in-time binding of an unbound concrete class is deprecated (ray-di/Ray.Di#337).
 *
 * The deprecation notice must be emitted exactly once, and only for a top-level
 * unnamed request of an instantiable concrete class:
 *
 * - internal (constructor) dependency            -> Unbound, no notice
 * - top-level getInstance(Concrete::class)       -> resolves, one E_USER_DEPRECATED
 * - named request getInstance(Concrete, 'name')  -> Unbound, no notice
 * - non-instantiable type                        -> Unbound, no notice
 */
class JitBindingDeprecationTest extends TestCase
{
    /** @var list<string> */
    private array $notices = [];

    protected function setUp(): void
    {
        $this->notices = [];
        set_error_handler(
            function (int $severity, string $message): bool {
                $this->notices[] = $message;

                return true;
            },
            E_USER_DEPRECATED,
        );
    }

    protected function tearDown(): void
    {
        restore_error_handler();
    }

    /** @return list<string> E_USER_DEPRECATED messages observed during $callback */
    private function captureNotice(callable $callback): array
    {
        $before = count($this->notices);
        $callback();

        return array_slice($this->notices, $before);
    }

    public function testTopLevelUnboundConcreteResolvesAndEmitsDeprecation(): void
    {
        $notices = $this->captureNotice(
            static fn () => (new Injector())->getInstance(FakeConstructCounter::class),
        );

        self::assertCount(1, $notices);
        self::assertStringContainsString(
            'Just-in-time binding of unbound concrete class "' . FakeConstructCounter::class . '" is deprecated',
            $notices[0],
        );
    }

    public function testInternalUnboundConcreteDependencyIsUnboundWithoutNotice(): void
    {
        // FakeJitDepConsumer implements FakeJitDepInterface but depends on an
        // unbound FakeJitDepConcrete. Resolving the bound interface top-level
        // never triggers JIT; the unbound interior dep surfaces as Unbound.
        $injector = new Injector(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeJitDepInterface::class)->to(FakeJitDepConsumer::class);
            }
        });

        $notices = $this->captureNotice(
            static function () use ($injector): void {
                try {
                    $injector->getInstance(FakeJitDepInterface::class);
                    self::fail('An internal unbound dependency must remain Unbound.');
                } catch (Unbound) {
                }
            },
        );

        self::assertSame([], $notices);
    }

    public function testNamedRequestIsUnboundWithoutNotice(): void
    {
        $injector = new Injector();

        $notices = $this->captureNotice(
            static function () use ($injector): void {
                try {
                    $injector->getInstance(FakeConstructCounter::class, 'no-such-name');
                    self::fail('A named request must remain Unbound.');
                } catch (Unbound) {
                }
            },
        );

        self::assertSame([], $notices);
    }

    public function testNonInstantiableInterfaceIsUnboundWithoutNotice(): void
    {
        $injector = new Injector();

        $notices = $this->captureNotice(
            static function () use ($injector): void {
                try {
                    $injector->getInstance(FakeEngineInterface::class);
                    self::fail('An interface must remain Unbound.');
                } catch (Unbound) {
                }
            },
        );

        self::assertSame([], $notices);
    }

    public function testNonInstantiableAbstractIsUnboundWithoutNotice(): void
    {
        $injector = new Injector();

        $notices = $this->captureNotice(
            static function () use ($injector): void {
                try {
                    $injector->getInstance(FakeAbstractClass::class);
                    self::fail('An abstract class must remain Unbound.');
                } catch (Unbound) {
                }
            },
        );

        self::assertSame([], $notices);
    }
}
