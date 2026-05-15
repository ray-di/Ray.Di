<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use stdClass;

class InstanceTest extends TestCase
{
    public function testToStringScalarString(): void
    {
        $instance = new Instance('hello');
        $this->assertSame('(string) hello', (string) $instance);
    }

    public function testToStringScalarInt(): void
    {
        $instance = new Instance(42);
        $this->assertSame('(integer) 42', (string) $instance);
    }

    public function testToStringObject(): void
    {
        $instance = new Instance(new stdClass());
        $this->assertSame('(object) stdClass', (string) $instance);
    }

    public function testToStringArray(): void
    {
        $instance = new Instance([1, 2, 3]);
        $this->assertSame('(array)', (string) $instance);
    }

    public function testToStringNull(): void
    {
        $instance = new Instance(null);
        $this->assertSame('(NULL)', (string) $instance);
    }
}
