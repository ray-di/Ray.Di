<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;

class DualReaderTest extends TestCase
{
    public function testGetConcreteClassWithModule(): void
    {
        $injector = new Injector(new FakeCarModule());
        $car = $injector->getInstance(FakeAttributeCar::class);
        $this->assertInstanceOf(FakeAttributeCar::class, $car);
    }
}
