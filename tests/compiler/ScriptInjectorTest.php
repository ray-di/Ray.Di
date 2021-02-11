<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PHPUnit\Framework\TestCase;
use Ray\Aop\WeavedInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Exception\Unbound;
use Ray\Di\InjectorInterface;
use Ray\Di\NullModule;

use function assert;
use function count;
use function glob;
use function serialize;
use function spl_object_hash;
use function unserialize;

class ScriptInjectorTest extends TestCase
{
    /** @var ScriptInjector */
    private $injector;

    protected function setUp(): void
    {
        deleteFiles(__DIR__ . '/tmp');
        $this->injector = new ScriptInjector(__DIR__ . '/tmp');
    }

    public function testGetInstance(): FakeCar
    {
        $diCompiler = new DiCompiler(new FakeCarModule(), __DIR__ . '/tmp');
        $diCompiler->compile();
        $car = $this->injector->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(FakeCar::class, $car);

        return $car;
    }

    /**
     * @depends testGetInstance
     */
    public function testDefaultValueInjected(FakeCar $car): void
    {
        $this->assertNull($car->null);
    }

    public function testCompileException(): void
    {
        $this->expectException(Unbound::class);
        $script = new ScriptInjector(__DIR__ . '/tmp');
        $script->getInstance('invalid-class');
    }

    public function testToPrototype(): void
    {
        (new DiCompiler(new FakeToBindPrototypeModule(), __DIR__ . '/tmp'))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertNotSame(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    public function testToSingleton(): void
    {
        (new DiCompiler(new FakeToBindSingletonModule(), __DIR__ . '/tmp'))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertSame($instance1, $instance2);
    }

    public function testToProviderPrototype(): void
    {
        (new DiCompiler(new FakeToProviderPrototypeModule(), __DIR__ . '/tmp'))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testToProviderSingleton(): void
    {
        (new DiCompiler(new FakeToProviderSingletonModule(), __DIR__ . '/tmp'))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertSame($instance1, $instance2);
    }

    public function testToInstancePrototype(): void
    {
        (new DiCompiler(new FakeToInstancePrototypeModule(), __DIR__ . '/tmp'))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testToInstanceSingleton(): void
    {
        (new DiCompiler(new FakeToInstanceSingletonModule(), __DIR__ . '/tmp'))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertSame($instance1, $instance2);
    }

    public function testSerializable(): void
    {
        $diCompiler = new DiCompiler(new FakeCarModule(), __DIR__ . '/tmp');
        $diCompiler->compile();
        $injector = unserialize(serialize($this->injector));
        $car = $injector->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(ScriptInjector::class, $injector);
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    public function testAop(): void
    {
        $compiler = new DiCompiler(new FakeCarModule(), __DIR__ . '/tmp');
        $compiler->compile();
        $injector = new ScriptInjector(__DIR__ . '/tmp');
        $instance1 = $injector->getInstance(FakeCarInterface::class);
        $instance2 = $injector->getInstance(FakeCar::class);
        $instance3 = $injector->getInstance(FakeCar2::class);
        assert($instance3 instanceof FakeCar2);
        $this->assertInstanceOf(WeavedInterface::class, $instance1);
        $this->assertInstanceOf(WeavedInterface::class, $instance2);
        $this->assertInstanceOf(WeavedInterface::class, $instance3);
        $this->assertInstanceOf(FakeRobot::class, $instance3->robot);
    }

    public function testOnDemandSingleton(): void
    {
        (new DiCompiler(new FakeToBindSingletonModule(), __DIR__ . '/tmp'))->compile();
        $dependSingleton1 = $this->injector->getInstance(FakeDependSingleton::class);
        assert($dependSingleton1 instanceof FakeDependSingleton);
        $dependSingleton2 = $this->injector->getInstance(FakeDependSingleton::class);
        assert($dependSingleton2 instanceof FakeDependSingleton);
        $hash1 = spl_object_hash($dependSingleton1->robot);
        $hash2 = spl_object_hash($dependSingleton2->robot);
        $this->assertSame($hash1, $hash2);
    }

    public function testOnDemandPrototype(): void
    {
        (new DiCompiler(new FakeCarModule(), __DIR__ . '/tmp'))->compile();
        $fakeDependPrototype1 = $this->injector->getInstance(FakeDependPrototype::class);
        assert($fakeDependPrototype1 instanceof FakeDependPrototype);
        $fakeDependPrototype2 = $this->injector->getInstance(FakeDependPrototype::class);
        assert($fakeDependPrototype2 instanceof FakeDependPrototype);
        $hash1 = spl_object_hash($fakeDependPrototype1->car);
        $hash2 = spl_object_hash($fakeDependPrototype2->car);
        $this->assertNotSame($hash1, $hash2);
    }

    public function testOptional(): void
    {
        $optional = $this->injector->getInstance(FakeOptional::class);
        assert($optional instanceof FakeOptional);
        $this->assertNull($optional->robot);
    }

    public function testDependInjector(): void
    {
        $diCompiler = new DiCompiler(new NullModule(), __DIR__ . '/tmp');
        $diCompiler->compile();
        $factory = $diCompiler->getInstance(FakeFactory::class);
        $this->assertInstanceOf(InjectorInterface::class, $factory->injector);
        $injector = new ScriptInjector(__DIR__ . '/tmp');
        $factory = $injector->getInstance(FakeFactory::class);
        $this->assertInstanceOf(InjectorInterface::class, $factory->injector);
    }

    public function testUnbound(): void
    {
        $this->expectException(Unbound::class);
        $this->expectExceptionMessage('NOCLASS-NONAME');
        $injector = new ScriptInjector(__DIR__ . '/tmp');
        $injector->getInstance('NOCLASS', 'NONAME');
    }

    public function testCompileOnDemand(): void
    {
        $injector = new ScriptInjector(
            __DIR__ . '/tmp',
            static function () {
                return new FakeCarModule();
            }
        );
        $car = $injector->getInstance(FakeCar::class);
        $this->assertTrue($car instanceof FakeCar);
    }

    public function testCompileOnDemandAop(): void
    {
        $injector = new ScriptInjector(
            __DIR__ . '/tmp',
            static function () {
                return new FakeAopModule();
            }
        );
        $aop = $injector->getInstance(FakeAopInterface::class);
        assert($aop instanceof FakeAopInterface);
        $result = $aop->returnSame(1);
        $this->assertSame(2, $result);
    }

    public function testCompileOnDemandSerialize(): void
    {
        $serialize = serialize(new ScriptInjector(
            __DIR__ . '/tmp',
            static function () {
                return new FakeCarModule();
            }
        ));
        $injector = unserialize($serialize);
        $car = $injector->getInstance(FakeCar::class);
        $this->assertTrue($car instanceof FakeCar);
    }

    public function testCompileOnDemandAopSerialize(): void
    {
        $injector = unserialize(serialize(new ScriptInjector(
            __DIR__ . '/tmp',
            static function () {
                return new FakeAopModule();
            }
        )));
        $aop = $injector->getInstance(FakeAopInterface::class);
        assert($aop instanceof FakeAopInterface);
        $result = $aop->returnSame(1);
        $this->assertSame(2, $result);
    }

    public function testClear(): void
    {
        $injector = new ScriptInjector(
            __DIR__ . '/tmp',
            static function () {
                return new FakeCarModule();
            }
        );
        $injector->getInstance(FakeCar::class);
        $count = count((array) glob(__DIR__ . '/tmp/*.php'));
        $this->assertGreaterThan(1, $count);
        $injector->clear();
        $countAfterClear = count((array) glob(__DIR__ . '/tmp/*.php'));
        $this->assertSame(0, $countAfterClear);
    }

    public function testNullObjectCompile(): ScriptInjector
    {
        $injector = new ScriptInjector(
            __DIR__ . '/tmp',
            static function () {
                return new FakeNullObjectModule();
            }
        );
        $instance = $injector->getInstance(FakeTyreInterface::class);
        $this->assertInstanceOf(FakeTyreInterface::class, $instance);

        return $injector;
    }

    /**
     * @runTestsInSeparateProcesses
     * @depends testNullObjectCompile
     */
    public function testNullObjectCompileCodeRead(ScriptInjector $injector): void
    {
        $instance = $injector->getInstance(FakeTyreInterface::class);
        $this->assertInstanceOf(FakeTyreInterface::class, $instance);
    }
}
