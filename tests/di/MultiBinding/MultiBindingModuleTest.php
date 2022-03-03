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
use Ray\Di\FakeRobot;
use Ray\Di\FakeRobotInterface;
use Ray\Di\FakeRobotProvider;
use Ray\Di\Injector;
use Ray\Di\MultiBinder;
use Ray\Di\NullModule;

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
        $this->module = new class extends AbstractModule {
            protected function configure(): void
            {
                $engineBinder = MultiBinder::newInstance($this, FakeEngineInterface::class);
                $engineBinder->addBinding('one')->to(FakeEngine::class);
                $engineBinder->addBinding('two')->to(FakeEngine2::class);
                $engineBinder->addBinding()->to(FakeEngine3::class);
                $robotBinder = MultiBinder::newInstance($this, FakeRobotInterface::class);
                $robotBinder->addBinding('to')->to(FakeRobot::class);
                $robotBinder->addBinding('provider')->toProvider(FakeRobotProvider::class);
                $robotBinder->addBinding('instance')->toInstance(new FakeRobot());
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

    public function testAnotherBinder(): void
    {
        $injector = new Injector($this->module);
        /** @var FakeMultiBindingConsumer $consumer */
        $consumer = $injector->getInstance(FakeMultiBindingConsumer::class);
        $this->assertInstanceOf(Map::class, $consumer->robots);
        $this->assertContainsOnlyInstancesOf(FakeRobot::class, $consumer->robots);
        $this->assertSame(3, count($consumer->robots));
    }

    public function testMultipileModule(): void
    {
        $module = new NullModule();
        $binder = MultiBinder::newInstance($module, FakeEngineInterface::class);
        $binder->addBinding('one')->to(FakeEngine::class);
        $binder->addBinding('two')->to(FakeEngine2::class);
        $module->install(new class extends AbstractModule {
            protected function configure()
            {
                $binder = MultiBinder::newInstance($this, FakeEngineInterface::class);
                $binder->addBinding('three')->to(FakeEngine::class);
                $binder->addBinding('four')->to(FakeEngine::class);
            }
        });
        /** @var MultiBindings $multiBindings */
        $multiBindings = $module->getContainer()->getInstance(MultiBindings::class);
        $this->assertArrayHasKey('one', (array) $multiBindings[FakeEngineInterface::class]);
        $this->assertArrayHasKey('two', (array) $multiBindings[FakeEngineInterface::class]);
        $this->assertArrayHasKey('three', (array) $multiBindings[FakeEngineInterface::class]);
        $this->assertArrayHasKey('four', (array) $multiBindings[FakeEngineInterface::class]);
    }
}
