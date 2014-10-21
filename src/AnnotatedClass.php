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
    public function __invoke(\ReflectionClass $class)
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
        $named = $this->reader->getMethodAnnotation($method, Named::class);
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
}
