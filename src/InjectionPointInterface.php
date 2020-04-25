<?php

declare(strict_types=1);

namespace Ray\Di;

interface InjectionPointInterface
{
    /**
     * Return parameter reflection
     */
    public function getParameter() : \ReflectionParameter;

    /**
     * Return method reflection
     */
    public function getMethod() : \ReflectionMethod;

    /**
     * Return class reflection
     *
     * @return \ReflectionClass<object>
     */
    public function getClass() : \ReflectionClass;

    /**
     * Return Qualifier annotations
     */
    public function getQualifiers() : array;
}
