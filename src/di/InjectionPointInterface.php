<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\ReflectionClass;
use Ray\Aop\ReflectionMethod;
use ReflectionParameter;

interface InjectionPointInterface
{
    /**
     * Return parameter reflection
     */
    public function getParameter(): ReflectionParameter;

    /**
     * Return method reflection
     */
    public function getMethod(): ReflectionMethod;

    /**
     * Return class reflection
     *
     * @psalm-return ReflectionClass
     * @phpstan-return ReflectionClass<object>
     */
    public function getClass(): ReflectionClass;

    /**
     * Return Qualifier annotations
     *
     * @return array<object>
     */
    public function getQualifiers(): array;
}
