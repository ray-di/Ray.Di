<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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

    /**
     * @param AnnotationReader $reader
     */
    public function __construct(AnnotationReader $reader)
    {
        AnnotationRegistry::registerFile(__DIR__ . '/DoctrineAnnotations.php');
        $this->reader = $reader;
        $this->injectionMethod = new AnnotatedClassMethods($reader);
    }

    /**
     * @param \ReflectionClass $class
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
     * @param \ReflectionClass $class
     *
     * @return null|\ReflectionMethod
     */
    public function getPostConstruct(\ReflectionClass $class)
    {
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $annotation = $this->reader->getMethodAnnotation($method, 'Ray\Di\Di\PostConstruct');
            if ($annotation) {
                return $method;
            }
        }

        return null;
    }
}
