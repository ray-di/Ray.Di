<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PhpParser\Node\Scalar\String_;
use PHPUnit\Framework\TestCase;

class NormalizerTest extends TestCase
{
    public function testString() : void
    {
        $normalizer = new Normalizer;
        $string = $normalizer('ray');
        if (! $string instanceof String_) {
            throw new \LogicException;
        }
        $this->assertInstanceOf(String_::class, $string);
        $this->assertSame('ray', $string->value);
    }

    public function testInvalidValue() : void
    {
        $this->expectException(\Ray\Compiler\Exception\InvalidInstance::class);

        $normalizer = new Normalizer;
        $resource = \fopen(__FILE__, 'r');
        $normalizer($resource);
    }
}
