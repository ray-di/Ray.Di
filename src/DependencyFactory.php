<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;

final class DependencyFactory
{
    /**
     * Create dependency object
     */
    public function newAnnotatedDependency(\ReflectionClass $class) : Dependency
    {
        $annotateClass = new AnnotatedClass(new AnnotationReader);
        $newInstance = $annotateClass->getNewInstance($class);
        $postConstruct = $annotateClass->getPostConstruct($class);
        $dependency = new Dependency($newInstance, $postConstruct);

        return $dependency;
    }

    /**
     * Create Provider binding
     */
    public function newProvider(\ReflectionClass $provider, string $context) : DependencyProvider
    {
        $dependency = $this->newAnnotatedDependency($provider);
        $dependency = new DependencyProvider($dependency, $context);

        return $dependency;
    }

    /**
     * Create ToConstructor binding
     */
    public function newToConstructor(
        \ReflectionClass $class,
        string $name,
        InjectionPoints $injectionPoints = null,
        \ReflectionMethod $postConstruct = null
    ) : Dependency {
        $setterMethods = $injectionPoints ? $injectionPoints($class->name) : new SetterMethods([]);
        $postConstruct = $postConstruct ? new \ReflectionMethod($class, $postConstruct) : null;
        $newInstance = new NewInstance($class, $setterMethods, new Name($name));
        $dependency = new Dependency($newInstance, $postConstruct);

        return $dependency;
    }
}
