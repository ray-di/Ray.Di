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
     *
     * @return \ReflectionParameter
     */
    public function getParameter();

    /**
     * Return method reflection
     *
     * @return \ReflectionFunctionAbstract
     */
    public function getMethod();

    /**
     * Return class reflection
     *
     * @return \ReflectionClass
     */
    public function getClass();

    /**
     * Return Qualifier annotations
     *
     * @return array
     */
    public function getQualifiers();
}
