<?php

/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\Qualifier;

final class InjectionPoint implements InjectionPointInterface
{
    /**
     * @var \ReflectionParameter
     */
    private $parameter;

    /**
     * @var Reader
     */
    private $reader;

    public function __construct(\ReflectionParameter $parameter, Reader $reader)
    {
        $this->parameter = $parameter;
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter() : \ReflectionParameter
    {
        return $this->parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod() : \ReflectionFunctionAbstract
    {
        return $this->parameter->getDeclaringFunction();
    }

    /**
     * {@inheritdoc}
     */
    public function getClass() : \ReflectionClass
    {
        return $this->parameter->getDeclaringClass();
    }

    /**
     * {@inheritdoc}
     */
    public function getQualifiers()
    {
        $qualifiers = [];
        /* @noinspection PhpParamsInspection */
        $annotations = $this->reader->getMethodAnnotations($this->getMethod());
        foreach ($annotations as $annotation) {
            $qualifier = $this->reader->getClassAnnotation(
                new \ReflectionClass($annotation),
                Qualifier::class
            );
            if ($qualifier instanceof Qualifier) {
                $qualifiers[] = $annotation;
            }
        }

        return $qualifiers;
    }
}
