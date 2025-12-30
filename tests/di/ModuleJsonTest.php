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
        $module = new FakeLogStringModule();
        $json = $module->toJson();
        $decoded = json_decode($json, true);

        $bindings = $decoded['bindings'];
        $interfaces = array_column($bindings, 'interface');

        $this->assertContains(FakeAopInterface::class, $interfaces);
        $this->assertContains(FakeRobotInterface::class, $interfaces);
    }

    public function testDependencyBinding(): void
    {
        $module = new FakeLogStringModule();
        $json = $module->toJson();
        $decoded = json_decode($json, true);

        $binding = $this->findBinding($decoded['bindings'], FakeAopInterface::class);

        $this->assertNotNull($binding);
        $this->assertSame('class', $binding['type']);
        $this->assertSame(FakeAop::class, $binding['to']);
    }

    public function testProviderBinding(): void
    {
        $module = new FakeLogStringModule();
        $json = $module->toJson();
        $decoded = json_decode($json, true);

        $binding = $this->findBinding($decoded['bindings'], FakeRobotInterface::class);

        $this->assertNotNull($binding);
        $this->assertSame('provider', $binding['type']);
        $this->assertSame(FakeRobotProvider::class, $binding['to']);
    }

    public function testInstanceBinding(): void
    {
        $module = new FakeLogStringModule();
        $json = $module->toJson();
        $decoded = json_decode($json, true);

        $binding = $this->findBindingByName($decoded['bindings'], 'string');

        $this->assertNotNull($binding);
        $this->assertSame('instance', $binding['type']);
        $this->assertSame('1', $binding['to']);
    }

    public function testAopBinding(): void
    {
        $module = new FakeLogStringModule();
        $json = $module->toJson();
        $decoded = json_decode($json, true);

        $binding = $this->findBinding($decoded['bindings'], FakeAopInterface::class);

        $this->assertNotNull($binding);
        $this->assertArrayHasKey('aop', $binding);
        $this->assertArrayHasKey('returnSame', $binding['aop']);
        $this->assertContains(FakeDoubleInterceptor::class, $binding['aop']['returnSame']);
    }

    /**
     * @param array<array{interface: string, name: string, type: string, to: mixed}> $bindings
     *
     * @return array{interface: string, name: string, type: string, to: mixed}|null
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
     * @param array<array{interface: string, name: string, type: string, to: mixed}> $bindings
     *
     * @return array{interface: string, name: string, type: string, to: mixed}|null
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
