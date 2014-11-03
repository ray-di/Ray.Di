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


    /**
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
