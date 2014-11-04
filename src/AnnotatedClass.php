<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;
use Ray\Di\Di\Qualifier;

final class AnnotatedClass
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @param AnnotationReader $reader
     */
    public function __construct(AnnotationReader $reader)
    {
        AnnotationRegistry::registerFile(__DIR__ . '/DoctrineAnnotations.php');
        $this->reader = $reader;
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
            $setterMethods->add($this->getSetterMethod($method));
        }
        $name = $this->getConstructorName($class);
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
            $annotation = $this->reader->getMethodAnnotation($method, PostConstruct::class);
            if ($annotation) {
                return $method;
            }
        }

        return null;
    }

    /**
     * @param \ReflectionMethod $method
     *
     * @return SetterMethod
     */
    private function getSetterMethod(\ReflectionMethod $method)
    {
        $inject = $this->reader->getMethodAnnotation($method, Inject::class);
        /** @var $inject \Ray\Di\Di\Inject */
        if (! $inject) {
            return null;
        }
        $named = $this->getMethodAnnotation($method);
        /** @var $named \Ray\Di\Di\Named */
        $name = $named ? $named->value : '';
        $setterMethod = new SetterMethod($method, new Name($name));
        if ($inject->optional) {
            $setterMethod->setOptional();
        }

        return $setterMethod;
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return Name
     */
    private function getConstructorName(\ReflectionClass $class)
    {
        $constructor = $class->getConstructor();
        if (! $constructor) {
            return new Name(Name::ANY);
        }
        $named = $this->reader->getMethodAnnotation($constructor, Named::class);
        if (! $named) {
            return new Name(Name::ANY);
        }
        /** @var $named Named */
        return new Name($named->value);
    }

    /**
     * @param \ReflectionMethod $method
     *
     * @return null|Named
     */
    private function getMethodAnnotation(\ReflectionMethod $method)
    {
        $bindAnnotation = $this->getBindAnnotation($method);
        if ($bindAnnotation) {

            return $bindAnnotation;
        }
        $namedAnnotation = $this->reader->getMethodAnnotation($method, Named::class);
        if ($namedAnnotation) {

            return $namedAnnotation;
        }

        return null;
    }

    /**
     * @param \ReflectionMethod $method
     *
     * @return null|Named
     */
    private function getBindAnnotation(\ReflectionMethod $method)
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            $bindAnnotation = $this->findBindAnnotation($annotation);
            if ($bindAnnotation) {

                return $bindAnnotation;
            }
        }

        return null;
    }

    /**
     * @param object $annotation
     *
     * @return null|Named
     */
    private function findBindAnnotation($annotation)
    {
        $bindingAnnotation = $this->reader->getClassAnnotation(new \ReflectionClass($annotation), Qualifier::class);
        if (! $bindingAnnotation) {

            return null;
        }
        $named = new Named;
        $named->value = get_class($annotation);

        return $named;
    }
}
