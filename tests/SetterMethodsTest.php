<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use PHPUnit\Framework\TestCase;

class SetterMethodsTest extends TestCase
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
