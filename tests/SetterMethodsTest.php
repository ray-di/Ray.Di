<?php

namespace Ray\Di;

class SetterMethodsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SetterMethod
     */
    protected $setterMethod;

    public function setUp()
    {
        $method = new \ReflectionMethod(FakeCar::class, 'setTires');
        $this->setterMethod = new SetterMethod($method, new Name(Name::ANY));
    }

    public function testInvoke()
    {
        $car = new FakeCar(new FakeEngine);
        $container = (new FakeCarModule)->getContainer();
        $this->setterMethod->__invoke($car, $container);
        $this->assertInstanceOf(FakeTyre::class, $car->frontTyre);
        $this->assertInstanceOf(FakeTyre::class, $car->rearTyre);
    }
}
