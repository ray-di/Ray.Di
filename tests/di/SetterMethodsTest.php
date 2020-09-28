<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class SetterMethodsTest extends TestCase
{
    /**
     * @var SetterMethod
     */
    protected $setterMethod;

    protected function setUp() : void
    {
        $method = new ReflectionMethod(FakeCar::class, 'setTires');
        $this->setterMethod = new SetterMethod($method, new Name(Name::ANY));
    }

    public function testInvoke() : void
    {
        $car = new FakeCar(new FakeEngine);
        $container = (new FakeCarModule)->getContainer();
        $this->setterMethod->__invoke($car, $container);
        $this->assertInstanceOf(FakeTyre::class, $car->frontTyre);
        $this->assertInstanceOf(FakeTyre::class, $car->rearTyre);
    }
}
