<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;

class DualReaderTest extends TestCase
{
    public function testPhp8Attribute(): FakePhp8Car
    {
        $injector = new Injector(new FakeCarModule());
        $car = $injector->getInstance(FakePhp8Car::class);
        $this->assertInstanceOf(FakePhp8Car::class, $car);

        return $car;
    }

    /**
     * @depends testPhp8Attribute
     */
    public function testNameParameter(FakePhp8Car $car): void
    {
        $this->assertInstanceOf(FakeMirrorRight::class, $car->rightMirror);
        $this->assertInstanceOf(FakeMirrorLeft::class, $car->leftMirror);
    }
}
