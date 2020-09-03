<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class NewInstanceTest extends TestCase
{
    /**
     * @var NewInstance
     */
    protected $newInstance;

    protected function setUp() : void
    {
        $class = new ReflectionClass(FakeCar::class);
        $setters = [];
        $name = new Name(Name::ANY);
        $setters[] = new SetterMethod(new ReflectionMethod(FakeCar::class, 'setTires'), $name);
        $setters[] = new SetterMethod(new ReflectionMethod(FakeCar::class, 'setHardtop'), $name);
        $setterMethods = new SetterMethods($setters);
        $this->newInstance = new NewInstance($class, $setterMethods);
    }

    public function testInvoke() : void
    {
        $container = new Container;
        (new Bind($container, FakeTyreInterface::class))->to(FakeTyre::class);
        (new Bind($container, FakeEngineInterface::class))->to(FakeEngine::class);
        (new Bind($container, FakeHardtopInterface::class))->to(FakeHardtop::class);
        /** @var \Ray\Di\FakeCar $car */
        $car = $this->newInstance->__invoke($container);
        $this->assertInstanceOf(FakeCar::class, $car);
        $this->assertInstanceOf(FakeTyre::class, $car->frontTyre);
        $this->assertInstanceOf(FakeTyre::class, $car->rearTyre);
        $this->assertInstanceOf(FakeHardtop::class, $car->hardtop);
    }
}
