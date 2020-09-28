<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PHPUnit\Framework\TestCase;
use Ray\Aop\WeavedInterface;
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
        delete_dir($_ENV['TMP_DIR']);
        $this->injector = new ScriptInjector($_ENV['TMP_DIR']);
    }

    public function testGetInstance(): FakeCar
    {
        $diCompiler = new DiCompiler(new FakeCarModule(), $_ENV['TMP_DIR']);
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
        $script = new ScriptInjector($_ENV['TMP_DIR']);
        $script->getInstance('invalid-class');
    }

    public function testToPrototype(): void
    {
        (new DiCompiler(new FakeToBindPrototypeModule(), $_ENV['TMP_DIR']))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertNotSame(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    public function testToSingleton(): void
    {
        (new DiCompiler(new FakeToBindSingletonModule(), $_ENV['TMP_DIR']))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertSame($instance1, $instance2);
    }

    public function testToProviderPrototype(): void
    {
        (new DiCompiler(new FakeToProviderPrototypeModule(), $_ENV['TMP_DIR']))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testToProviderSingleton(): void
    {
        (new DiCompiler(new FakeToProviderSingletonModule(), $_ENV['TMP_DIR']))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertSame($instance1, $instance2);
    }

    public function testToInstancePrototype(): void
    {
        (new DiCompiler(new FakeToInstancePrototypeModule(), $_ENV['TMP_DIR']))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testToInstanceSingleton(): void
    {
        (new DiCompiler(new FakeToInstanceSingletonModule(), $_ENV['TMP_DIR']))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertSame($instance1, $instance2);
    }

    public function testSerializable(): void
    {
        $diCompiler = new DiCompiler(new FakeCarModule(), $_ENV['TMP_DIR']);
        $diCompiler->compile();
        $injector = unserialize(serialize($this->injector));
        $car = $injector->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(ScriptInjector::class, $injector);
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    public function testAop(): void
    {
        $compiler = new DiCompiler(new FakeCarModule(), $_ENV['TMP_DIR']);
        $compiler->compile();
        $injector = new ScriptInjector($_ENV['TMP_DIR']);
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
        (new DiCompiler(new FakeToBindSingletonModule(), $_ENV['TMP_DIR']))->compile();
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
        (new DiCompiler(new FakeCarModule(), $_ENV['TMP_DIR']))->compile();
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
        $diCompiler = new DiCompiler(new NullModule(), $_ENV['TMP_DIR']);
        $diCompiler->compile();
        $factory = $diCompiler->getInstance(FakeFactory::class);
        $this->assertInstanceOf(InjectorInterface::class, $factory->injector);
        /** @var FakeFactory $optional */
        $injector = new ScriptInjector($_ENV['TMP_DIR']);
        $factory = $injector->getInstance(FakeFactory::class);
        $this->assertInstanceOf(InjectorInterface::class, $factory->injector);
    }

    public function testUnbound(): void
    {
        $this->expectException(Unbound::class);
        $this->expectExceptionMessage('NOCLASS-NONAME');
        $injector = new ScriptInjector($_ENV['TMP_DIR']);
        $injector->getInstance('NOCLASS', 'NONAME');
    }

    public function testCompileOnDemand(): void
    {
        $injector = new ScriptInjector(
            $_ENV['TMP_DIR'],
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
            $_ENV['TMP_DIR'],
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
            $_ENV['TMP_DIR'],
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
            $_ENV['TMP_DIR'],
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
            $_ENV['TMP_DIR'],
            static function () {
                return new FakeCarModule();
            }
        );
        $injector->getInstance(FakeCar::class);
        $count = count((array) glob($_ENV['TMP_DIR'] . '/*'));
        $this->assertGreaterThan(0, $count);
        $injector->clear();
        $countAfterClear = count((array) glob($_ENV['TMP_DIR'] . '/*'));
        $this->assertSame(0, $countAfterClear);
    }
}
