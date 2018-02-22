<?php

/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
     */
    public function getClass() : \ReflectionClass;

    /**
     * Return Qualifier annotations
     */
    public function getQualifiers() : array;
}
