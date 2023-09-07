<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\ReflectionClass;
use ReflectionMethod;

use function assert;
use function class_exists;

final class DependencyFactory
{
    /**
     * Create dependency object
     *
     * @param ReflectionClass<object> $class
     */
    public function newAnnotatedDependency(ReflectionClass $class): Dependency
    {
        $annotateClass = new AnnotatedClass();
        $newInstance = $annotateClass->getNewInstance($class);
        $postConstruct = $annotateClass->getPostConstruct($class);

        return new Dependency($newInstance, $postConstruct);
    }

    /**
     * Create Provider binding
     *
     * @param ReflectionClass<object> $provider
     */
    public function newProvider(ReflectionClass $provider, string $context): DependencyProvider
    {
        $dependency = $this->newAnnotatedDependency($provider);

        return new DependencyProvider($dependency, $context);
    }

    /**
     * Create ToConstructor binding
     *
     * @param ReflectionClass<object> $class
     */
    public function newToConstructor(
        ReflectionClass $class,
        string $name,
        ?InjectionPoints $injectionPoints = null,
        ?ReflectionMethod $postConstruct = null
    ): Dependency {
        assert(class_exists($class->name));
        $setterMethods = $injectionPoints ? $injectionPoints($class->name) : new SetterMethods([]);
        $newInstance = new NewInstance($class, $setterMethods, new Name($name));

        return new Dependency($newInstance, $postConstruct);
    }
}
