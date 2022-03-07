<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use PHPUnit\Framework\TestCase;
use Ray\Di\Di\Set;
use Ray\Di\FakeMultiBindingAnnotation;
use Ray\Di\ParameterAttributeReader;
use ReflectionParameter;

class ParameterAttributeReaderTest extends TestCase
{
    public function testGet(): void
    {
        $reader = new ParameterAttributeReader();
        $prop = $reader->get(
            new ReflectionParameter(
                [FakeMultiBindingAnnotation::class, '__construct'],
                'engines'
            ),
            Set::class
        );
        $this->assertInstanceOf(Set::class, $prop);
    }
}
