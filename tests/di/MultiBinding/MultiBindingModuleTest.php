<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use LogicException;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\FakeEngine;
use Ray\Di\FakeEngine2;
use Ray\Di\FakeEngine3;
use Ray\Di\FakeEngineInterface;
use Ray\Di\FakeMultiBindingConsumer;
use Ray\Di\Injector;
use Ray\Di\MultiBinder;

use function count;

/**
 * @requires PHP 8.0
 */
class MultiBindingModuleTest extends TestCase
{
    /** @var AbstractModule */
    private $module;

    protected function setUp(): void
    {
        $this->module = new class extends AbstractModule{
            protected function configure(): void
            {
                $binder = MultiBinder::newInstance($this, FakeEngineInterface::class);
                $binder->add(FakeEngine::class, 'one');
                $binder->add(FakeEngine2::class, 'two');
                $binder->add(FakeEngine3::class);
            }
        };
    }

    public function testInjectMap(): Map
    {
        $injector = new Injector($this->module);
        /** @var FakeMultiBindingConsumer $consumer */
        $consumer = $injector->getInstance(FakeMultiBindingConsumer::class);
        $this->assertInstanceOf(Map::class, $consumer->engines);

        return $consumer->engines;
    }

    /**
     * @depends testInjectMap
     */
    public function testMapInstance(Map $map): void
    {
        $this->assertInstanceOf(FakeEngine::class, $map['one']);
        $this->assertInstanceOf(FakeEngine2::class, $map['two']);
    }

    /**
     * @depends testInjectMap
     */
    public function testMapIteration(Map $map): void
    {
        $this->assertContainsOnlyInstancesOf(FakeEngineInterface::class, $map);

        $this->assertSame(3, count($map));
    }

    /**
     * @depends testInjectMap
     */
    public function testIsSet(Map $map): void
    {
        $this->assertTrue(isset($map['one']));
        $this->assertTrue(isset($map['two']));
    }

    /**
     * @depends testInjectMap
     */
    public function testOffsetSet(Map $map): void
    {
        $this->expectException(LogicException::class);
        $map['one'] = 1;
    }

    /**
     * @depends testInjectMap
     */
    public function testOffsetUnset(Map $map): void
    {
        $this->expectException(LogicException::class);
        unset($map['one']);
    }
}
