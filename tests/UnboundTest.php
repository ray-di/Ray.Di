<?php

declare(strict_types=1);

namespace Ray\Di;

use LogicException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\Unbound;

class UnboundTest extends TestCase
{
    public function testGetBound() : void
    {
        $previous = new Unbound('dep1-');
        $e = new Unbound('dep2-', 0, $previous);
        $string = (string) $e;
        $this->assertStringContainsString('Ray\\Di\\Exception\\Unbound', $string);
        $this->assertStringContainsString('dep1-', $string);
        $this->assertStringContainsString('dep2-', $string);
    }

    public function testNoPrevious() : void
    {
        $e = new Unbound('dep0-', 0);
        $string = (string) $e;
        $this->assertStringContainsString('Ray\\Di\\Exception\\Unbound', $string);
    }

    public function testNonUnboundPrevious() : void
    {
        $string = (string) new Unbound('', 0, new LogicException);
        $expected = 'LogicException';
        $this->assertStringContainsString($expected, $string);
    }
}
