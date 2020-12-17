<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;

class DualReaderTest extends TestCase
{
    public function testPhp8Attribute(): void
    {
        $injector = new Injector(new FakeCarModule());
        $car = $injector->getInstance(FakePhp8Car::class);
        $this->assertInstanceOf(FakePhp8Car::class, $car);
    }
}
