<?php

namespace Ray\Di;

use Ray\Di\Exception\Unbound;

class InjectorTest extends \PHPUnit_Framework_TestCase
{
    public function testNew()
    {
        $injector = new Injector(new FakeInstanceBindModule);
        $this->assertInstanceOf(Injector::class, $injector);
    }

    public function testGetToInstance()
    {
        $injector = new Injector(new FakeInstanceBindModule);
        $instance = $injector->getInstance('', 'one');
        $this->assertSame(1, $instance);
    }

    public function testUnbound()
    {
        $this->setExpectedException(Unbound::class);
        $injector = new Injector(new FakeInstanceBindModule);
        $injector->getInstance('', 'invalid-binding-xxx');
    }

    public function testInstall()
    {
        $injector = new Injector(new FakeInstallModule());
        $instance = $injector->getInstance('', 'one');
        $this->assertSame(1, $instance);
        $instance = $injector->getInstance('', 'two');
        $this->assertSame(2, $instance);
    }

    public function testFormerBindingHasPriority()
    {
        $injector = new Injector(new FakeFormerBindingHasPriorityModule);
        $instance = $injector->getInstance('', 'one');
        $this->assertSame(1, $instance);
    }

    public function testLatterBindingHasPriorityWithThisParameter()
    {
        $injector = new Injector(new FakeOverrideInstallModule);
        $instance = $injector->getInstance('', 'one');
        $this->assertSame(3, $instance);
    }

    public function testModuleInModule()
    {
        $injector = new Injector(new FakeModuleInModule);
        $instance = $injector->getInstance('', 'one');
        $this->assertSame(1, $instance);
        $instance = $injector->getInstance('', 'two');
        $this->assertSame(2, $instance);
    }

    public function testModuleInModuleOverride()
    {
        $injector = new Injector(new FakeModuleInModuleOverride);
        $instance = $injector->getInstance('', 'one');
        $this->assertSame(3, $instance);
    }

    public function testToBinding()
    {
        $injector = new Injector(new FakeToBindModule);
        $instance = $injector->getInstance(FakeRobotInterface::class);
        $this->assertInstanceOf(FakeRobot::class, $instance);
    }

    public function testToBindingPrototype()
    {
        $injector = new Injector(new FakeToBindModule);
        $instance1 = $injector->getInstance(FakeRobotInterface::class);
        $instance2 = $injector->getInstance(FakeRobotInterface::class);
        $this->assertNotEquals(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    public function testToBindingSingleton()
    {
        $injector = new Injector(new FakeToBindSingletonModule);
        $instance1 = $injector->getInstance(FakeRobotInterface::class);
        $instance2 = $injector->getInstance(FakeRobotInterface::class);
        $this->assertSame(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    public function testToProviderBinding()
    {
        $injector = new Injector(new FakeToProviderBindModule);
        $instance1 = $injector->getInstance(FakeRobotInterface::class);
        $instance2 = $injector->getInstance(FakeRobotInterface::class);
        $this->assertNotEquals(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    public function testToProviderBindingSingleton()
    {
        $injector = new Injector(new FakeToProviderSingletonBindModule);
        $instance1 = $injector->getInstance(FakeRobotInterface::class);
        $instance2 = $injector->getInstance(FakeRobotInterface::class);
        $this->assertSame(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    public function testExplicitBindingInjection()
    {
        $injector = new Injector(new FakeExplicitCarModule);
        $car = $injector->getInstance(FakeCarInterface::class);
        /** @var $car FakeCar */
        $this->assertInstanceOf(FakeCar::class, $car);
        $this->assertInstanceOf(FakeTyre::class, $car->frontTyre);
        $this->assertInstanceOf(FakeTyre::class, $car->rearTyre);
        $this->assertInstanceOf(FakeHardtop::class, $car->hardtop);
    }

    public function testGetConcreteClass()
    {
        $injector = new Injector;
        $robot = $injector->getInstance(FakeRobot::class);
        $this->assertInstanceOf(FakeRobot::class, $robot);
    }

    public function testGetConcretHavingDependency()
    {
        $injector = new Injector;
        $team = $injector->getInstance(FakeRobotTeam::class);
        /** @var $team FakeRobotTeam */
        $this->assertInstanceOf(FakeRobotTeam::class, $team);
        $this->assertInstanceOf(FakeRobot::class, $team->robot1);
        $this->assertInstanceOf(FakeRobot::class, $team->robot2);
    }

    public function testGetConcreteClassWithModule()
    {
        $injector = new Injector(new FakeCarModule);
        $car = $injector->getInstance(FakeCar::class);
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    public function testAnnotationBasedInjection()
    {
        $injector = new Injector(new FakeCarModule);
        $car = $injector->getInstance(FakeCarInterface::class);
        /** @var $car FakeCar */
        $this->assertInstanceOf(FakeCar::class, $car);
        $this->assertInstanceOf(FakeTyre::class, $car->frontTyre);
        $this->assertInstanceOf(FakeTyre::class, $car->rearTyre);
        $this->assertInstanceOf(FakeHardtop::class, $car->hardtop);
        $this->assertInstanceOf(FakeMirrorInterface::class, $car->rightMirror);
        $this->assertInstanceOf(FakeMirrorInterface::class, $car->spareMirror);
        $this->assertSame(spl_object_hash($car->rightMirror), spl_object_hash($car->spareMirror));
        $this->assertInstanceOf(FakeHandle::class, $car->handle);
        $this->assertSame($car->handle->logo, 'momo');

        return $injector;
    }

    /**
     * @depends testAnnotationBasedInjection
     */
    public function testSerialize(Injector $injector)
    {
        $extractedInjector = unserialize(serialize($injector));
        $car = $extractedInjector->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    public function testAop()
    {
        $injector = new Injector(new FakeAopModule, $_ENV['TMP_DIR']);
        $instance = $injector->getInstance(FakeAopInterface::class);
        /** @var $instance FakeAop */
        $result = $instance->returnSame(2);
        $this->assertSame(4, $result);
    }
}
