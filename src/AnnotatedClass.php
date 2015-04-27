<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

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
        AnnotationRegistry::registerFile(__DIR__ . '/DoctrineAnnotations.php');
        $this->reader = $reader;
        $this->injectionMethod = new AnnotatedClassMethods($reader);
    }

    /**
     * Return factory instance
     *
     * @param \ReflectionClass $class Target class reflection
     *
     * @return NewInstance
     */
    public function getNewInstance(\ReflectionClass $class)
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
        $newInstance = new NewInstance($class, $setterMethods, $name);

        return $newInstance;
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
            /** @var $annotation \Ray\Di\Di\PostConstruct|null */
            $annotation = $this->reader->getMethodAnnotation($method, 'Ray\Di\Di\PostConstruct');
            if ($annotation) {
                return $method;
            }
        }

        return null;
    }
}
