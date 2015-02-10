<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
     * @return \ReflectionMethod
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
