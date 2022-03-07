<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use PHPUnit\Framework\TestCase;
use Ray\Di\Di\Set;
use Ray\Di\FakeMultiBindingAnnotation;
use Ray\Di\ParameterReader;
use ReflectionParameter;

class ParameterReaderTest extends TestCase
{
    /** @var ParameterReader  */
    private $reader;

    protected function setUp(): void
    {
        $this->reader = new ParameterReader();
    }

    public function testGetParametrAnnotation(): void
    {
        $prop = $this->reader->getParametrAnnotation(
            new ReflectionParameter(
                [FakeMultiBindingAnnotation::class, '__construct'],
                'engines'
            ),
            Set::class
        );
        $this->assertInstanceOf(Set::class, $prop);
    }
}
