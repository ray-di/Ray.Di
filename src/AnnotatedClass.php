<?php

/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Di\PostConstruct;

final class AnnotatedClass
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @var AnnotatedClassMethods
     */
    private $injectionMethod;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
        $this->injectionMethod = new AnnotatedClassMethods($reader);
    }

    /**
     * Return factory instance
     *
     * @param \ReflectionClass $class Target class reflection
     */
    public function getNewInstance(\ReflectionClass $class) : NewInstance
    {
        $setterMethods = new SetterMethods([]);
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            if ($method->name === '__construct') {
                continue;
            }
            $setterMethods->add($this->injectionMethod->getSetterMethod($method));
        }
        $name = $this->injectionMethod->getConstructorName($class);
        return new NewInstance($class, $setterMethods, $name);
    }

    /**
     * Return @-PostConstruct method reflection
     *
     * @param \ReflectionClass $class
     *
     * @return null|\ReflectionMethod
     */
    public function getPostConstruct(\ReflectionClass $class)
    {
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            /* @var $annotation PostConstruct|null */
            $annotation = $this->reader->getMethodAnnotation($method, PostConstruct::class);
            if ($annotation instanceof PostConstruct) {
                return $method;
            }
        }

        return null;
    }
}
