<?php
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
     *
     * @param \ReflectionClass $class
     *
     * @return Dependency
     */
    public function newAnnotatedDependency(\ReflectionClass $class)
    {
        $annotateClass = new AnnotatedClass(new AnnotationReader);
        $newInstance = $annotateClass->getNewInstance($class);
        $postConstruct = $annotateClass->getPostConstruct($class);
        $dependency = new Dependency($newInstance, $postConstruct);

        return $dependency;
    }

    /**
     * Create Provider binding
     *
     * @param \ReflectionClass $provider
     *
     * @return DependencyProvider
     */
    public function newProvider(\ReflectionClass $provider, $context)
    {
        $dependency = $this->newAnnotatedDependency($provider);
        $dependency = new DependencyProvider($dependency, $context);

        return $dependency;
    }

    /**
     * Create ToConstructor binding
     *
     * @param \ReflectionClass  $class
     * @param string            $name
     * @param InjectionPoints   $injectionPoints
     * @param \ReflectionMethod $postConstruct
     *
     * @return Dependency
     */
    public function newToConstructor(
        \ReflectionClass $class,
        $name,
        InjectionPoints $injectionPoints = null,
        \ReflectionMethod $postConstruct = null
    ) {
        $setterMethods = $injectionPoints ? $injectionPoints($class->name) : new SetterMethods([]);
        $postConstruct = $postConstruct ? new \ReflectionMethod($class, $postConstruct) : null;
        $newInstance = new NewInstance($class, $setterMethods, new Name($name));
        $dependency = new Dependency($newInstance, $postConstruct);

        return $dependency;
    }
}
