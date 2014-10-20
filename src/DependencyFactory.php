<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;

final class DependencyFactory
{
    /**
     * @param \ReflectionClass $class
     *
     * @return Dependency
     */
    public function newAnnotatedDependency(\ReflectionClass $class)
    {
        $newInstance = (new AnnotatedClass(new AnnotationReader))->__invoke($class);
        $dependency = new Dependency($newInstance);

        return $dependency;
    }

    /**
     * @param \ReflectionClass  $class
     * @param InjectionPoints   $injectionPoints
     * @param \ReflectionMethod $postConstruct
     *
     * @return Dependency
     */
    public function newExplicit(
        \ReflectionClass $class,
        InjectionPoints $injectionPoints,
        \ReflectionMethod $postConstruct
    ) {
        $newInstance = new NewInstance($class, $injectionPoints($class->name), new Name(Name::ANY));
        $dependency = new Dependency($newInstance, $postConstruct);

        return $dependency;
    }

    /**
     * @param \ReflectionClass $provider
     *
     * @return Provider
     */
    public function newProvider(\ReflectionClass $provider)
    {
        $dependency = $this->newAnnotatedDependency($provider);
        $dependency = new Provider($dependency);

        return $dependency;
    }
}
