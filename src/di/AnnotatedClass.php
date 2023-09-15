<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\ReflectionClass;
use Ray\Di\Di\PostConstruct;
use ReflectionMethod;

final class AnnotatedClass
{
    /** @var AnnotatedClassMethods */
    private $injectionMethod;

    public function __construct()
    {
        $this->injectionMethod = new AnnotatedClassMethods();
    }

    /**
     * Return factory instance
     *
     * @phpstan-param ReflectionClass<object> $class Target class reflection
     */
    public function getNewInstance(ReflectionClass $class): NewInstance
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
     * @phpstan-param ReflectionClass<object> $class
     */
    public function getPostConstruct(ReflectionClass $class): ?ReflectionMethod
    {
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $annotation = $method->getAnnotation(PostConstruct::class);
            if ($annotation instanceof PostConstruct) {
                return $method;
            }
        }

        return null;
    }
}
