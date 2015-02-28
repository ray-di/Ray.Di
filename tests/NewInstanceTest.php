<?php

namespace Ray\Di;

class NewInstanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NewInstance
     */
    protected $newInstance;

    public function setUp()
    {
        $class = new \ReflectionClass(FakeCar::class);
        $setters = [];
        $name = new Name(Name::ANY);
        $setters[] = new SetterMethod(new \ReflectionMethod(FakeCar::class, 'setTires'), $name);
        $setters[] = new SetterMethod(new \ReflectionMethod(FakeCar::class, 'setHardtop'), $name);
        $setterMethods = new SetterMethods($setters);
        $this->newInstance = new NewInstance($class, $setterMethods);
    }

    public function testInvoke()
    {
        $container = new Container;
        (new Bind($container, FakeTyreInterface::class))->to(FakeTyre::class);
        (new Bind($container, FakeEngineInterface::class))->to(FakeEngine::class);
        (new Bind($container, FakeHardtopInterface::class))->to(FakeHardtop::class);
        $car = $this->newInstance->__invoke($container);
        /* @var $car FakeCar */
        $this->assertInstanceOf(FakeCar::class, $car);
        $this->assertInstanceOf(FakeTyre::class, $car->frontTyre);
        $this->assertInstanceOf(FakeTyre::class, $car->rearTyre);
        $this->assertInstanceOf(FakeHardtop::class, $car->hardtop);
    }
}
