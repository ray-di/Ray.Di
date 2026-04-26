<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;

use function array_column;
use function json_decode;

class ModuleJsonTest extends TestCase
{
    public function testToJson(): void
    {
        $module = new FakeLogStringModule();
        $json = $module->toJson();
        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('bindings', $decoded);
        $this->assertIsArray($decoded['bindings']);
    }

    public function testBindingsContainInterface(): void
    {
        $bindings = $this->decodeBindings();
        $interfaces = array_column($bindings, 'interface');

        $this->assertContains(FakeAopInterface::class, $interfaces);
        $this->assertContains(FakeRobotInterface::class, $interfaces);
    }

    public function testDependencyBinding(): void
    {
        $binding = $this->findBinding($this->decodeBindings(), FakeAopInterface::class);

        $this->assertNotNull($binding);
        $this->assertSame('class', $binding['type']);
        $this->assertSame(FakeAop::class, $binding['to']);
    }

    public function testProviderBinding(): void
    {
        $binding = $this->findBinding($this->decodeBindings(), FakeRobotInterface::class);

        $this->assertNotNull($binding);
        $this->assertSame('provider', $binding['type']);
        $this->assertSame(FakeRobotProvider::class, $binding['to']);
    }

    public function testInstanceBinding(): void
    {
        $binding = $this->findBindingByName($this->decodeBindings(), 'string');

        $this->assertNotNull($binding);
        $this->assertSame('instance', $binding['type']);
        $this->assertSame('1', $binding['to']);
    }

    public function testAopBinding(): void
    {
        $binding = $this->findBinding($this->decodeBindings(), FakeAopInterface::class);

        $this->assertNotNull($binding);
        $this->assertArrayHasKey('aop', $binding);
        $aop = $binding['aop'] ?? [];
        $this->assertArrayHasKey('returnSame', $aop);
        $this->assertContains(FakeDoubleInterceptor::class, $aop['returnSame']);
    }

    public function testToNullBinding(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeRobotInterface::class)->toNull();
            }
        };
        $json = $module->toJson();
        /** @var array{bindings: list<array{interface: string, name: string, type: string, to: mixed}>} $decoded */
        $decoded = json_decode($json, true);
        $binding = null;
        foreach ($decoded['bindings'] as $b) {
            if ($b['interface'] === FakeRobotInterface::class) {
                $binding = $b;
                break;
            }
        }

        $this->assertNotNull($binding);
        $this->assertSame('null', $binding['type']);
        $this->assertNull($binding['to']);
    }

    /**
     * @return list<array{interface: string, name: string, type: string, to: mixed, aop?: array<string, list<string>>}>
     */
    private function decodeBindings(): array
    {
        $module = new FakeLogStringModule();
        $json = $module->toJson();
        /** @var array{bindings: list<array{interface: string, name: string, type: string, to: mixed, aop?: array<string, list<string>>}>} $decoded */
        $decoded = json_decode($json, true);

        return $decoded['bindings'];
    }

    /**
     * @param list<array{interface: string, name: string, type: string, to: mixed, aop?: array<string, list<string>>}> $bindings
     *
     * @return array{interface: string, name: string, type: string, to: mixed, aop?: array<string, list<string>>}|null
     */
    private function findBinding(array $bindings, string $interface): ?array
    {
        foreach ($bindings as $binding) {
            if ($binding['interface'] === $interface) {
                return $binding;
            }
        }

        return null;
    }

    /**
     * @param list<array{interface: string, name: string, type: string, to: mixed, aop?: array<string, list<string>>}> $bindings
     *
     * @return array{interface: string, name: string, type: string, to: mixed, aop?: array<string, list<string>>}|null
     */
    private function findBindingByName(array $bindings, string $name): ?array
    {
        foreach ($bindings as $binding) {
            if ($binding['name'] === $name) {
                return $binding;
            }
        }

        return null;
    }
}
