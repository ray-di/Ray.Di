<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Ray\Di\ConstractorParamDualReader;
use Ray\Di\Di\Set;
use Ray\Di\FakeMultiBindingAnnotation;
use ReflectionParameter;

class ConstractorParamDualReaderTest extends TestCase
{
    public function testGet(): void
    {
        $reader = new ParameterDualReader();
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
