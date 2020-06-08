<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Qualifier;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

interface InjectionPointInterface
{
    /**
     * Return parameter reflection
     */
    public function getParameter() : ReflectionParameter;

    /**
     * Return method reflection
     */
    public function getMethod() : ReflectionMethod;

    /**
     * Return class reflection
     *
     * @phpstan-return \ReflectionClass<object>
     * @psalm-return \ReflectionClass
     */
    public function getClass() : ReflectionClass;

    /**
     * Return Qualifier annotations
     *
     * @return array<object>
     */
    public function getQualifiers() : array;
}
