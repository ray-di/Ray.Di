<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;

class InjectionPointsTest extends TestCase
{
    /**
     * @var InjectionPoints
     */
    protected $injectionPoints;

    protected function setUp() : void
    {
        parent::setUp();
        $this->injectionPoints = (new InjectionPoints)->addMethod('setTires')->addOptionalMethod('setHardtop');
    }

    public function testNew() : void
    {
        $this->assertInstanceOf(InjectionPoints::class, $this->injectionPoints);
    }

    public function testInvoke() : SetterMethods
    {
        $car = new FakeCar(new FakeEngine);
        $setterMethods = $this->injectionPoints->__invoke(get_class($car));
        $this->assertInstanceOf(SetterMethods::class, $setterMethods);

        return $setterMethods;
    }

    /**
     * @depends testInvoke
     */
    public function testSetterMethod(SetterMethods $setterMethod) : void
    {
        $car = new FakeCar(new FakeEngine);
        $container = (new FakeCarModule)->getContainer();
        $setterMethod($car, $container);
        $this->assertInstanceOf(FakeTyre::class, $car->frontTyre);
        $this->assertInstanceOf(FakeTyre::class, $car->rearTyre);
        $this->assertInstanceOf(FakeHardtop::class, $car->hardtop);
    }

    /**
     * @depends testInvoke
     */
    public function testSetterMethodOptional(SetterMethods $setterMethod) : void
    {
        $car = new FakeCar(new FakeEngine);
        // no hardtop installed with this module
        $container = (new FakeOpenCarModule)->getContainer();
        $setterMethod($car, $container);
        $this->assertInstanceOf(FakeTyre::class, $car->frontTyre);
        $this->assertInstanceOf(FakeTyre::class, $car->rearTyre);
        $this->assertNull($car->hardtop);
    }
}
