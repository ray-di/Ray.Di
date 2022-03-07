<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use PHPUnit\Framework\TestCase;
use Ray\Di\ConstractorParamDualReader;
use Ray\Di\Di\Set;
use Ray\Di\FakeMultiBindingAnnotation;
use ReflectionParameter;

class ParameterAttributeReaderTest extends TestCase
{
    public function testGet(): void
    {
        $reader = new ConstractorParamDualReader();
        $prop = $reader->getParametrAnnotation(
            new ReflectionParameter(
                [FakeMultiBindingAnnotation::class, '__construct'],
                'engines'
            ),
            Set::class
        );
        $this->assertInstanceOf(Set::class, $prop);
    }
}
