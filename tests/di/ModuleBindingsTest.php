<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Bindings\BindingInfo;

class ModuleBindingsTest extends TestCase
{
    public function testBindingInfoSchema(): void
    {
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind('')->annotatedWith('test')->toInstance('value');
            }
        };

        $container = $module->getContainer();
        $moduleBindings = new ModuleBindings();
        $bindings = $moduleBindings($container, []);

        // Validate schema
        $this->assertIsArray($bindings);
        $this->assertCount(1, $bindings);
        
        $binding = $bindings[0];
        $this->assertInstanceOf(BindingInfo::class, $binding);
        
        // Test JSON serialization matches schema
        $json = json_encode($bindings);
        $this->assertIsString($json);
        
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        
        // Validate required fields exist
        $bindingData = $decoded[0];
        $this->assertArrayHasKey('interface', $bindingData);
        $this->assertArrayHasKey('named', $bindingData);
        $this->assertArrayHasKey('type', $bindingData);
        $this->assertArrayHasKey('target', $bindingData);
        
        // Validate types match schema
        $this->assertIsString($bindingData['interface']);
        $this->assertTrue(is_string($bindingData['named']) || is_null($bindingData['named']));
        $this->assertContains($bindingData['type'], ['to', 'toProvider', 'toInstance']);
    }
}