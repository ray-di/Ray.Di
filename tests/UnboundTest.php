<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\Unbound;

class UnboundTest extends TestCase
{
    public function testGetBound()
    {
        $previous = new Unbound('dep1-');
        $e = new Unbound('dep2-', 0, $previous);
        $string = (string) $e;
        $this->assertContains("Ray\Di\Exception\Unbound", $string);
        $this->assertContains('dep1-', $string);
        $this->assertContains('dep2-', $string);
    }

    public function testNoPrevious()
    {
        $e = new Unbound('dep0-', 0);
        $string = (string) $e;
        $this->assertContains("Ray\Di\Exception\Unbound", $string);
    }

    public function testNonUnboundPrevious()
    {
        $string = (string) new Unbound('', 0, new \LogicException);
        $expected = 'LogicException';
        $this->assertContains($expected, $string);
    }
}
