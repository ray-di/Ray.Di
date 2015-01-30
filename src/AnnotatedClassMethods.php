<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\Named;

final class AnnotatedClassMethods
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return Name
     */
    public function getConstructorName(\ReflectionClass $class)
    {
        $constructor = $class->getConstructor();
        if (! $constructor) {
            return new Name(Name::ANY);
        }
        $named = $this->reader->getMethodAnnotation($constructor, 'Ray\Di\Di\Named');
        if ($named) {
            /** @var $named Named */
            return new Name($named->value);
        }
        $qualifierAnnotation = $this->getMethodAnnotation($constructor);
        if ($qualifierAnnotation) {
            return new Name($qualifierAnnotation->value);
        }
        return new Name(Name::ANY);
    }

    /**
     * @param \ReflectionMethod $method
     *
     * @return SetterMethod
     */
    public function getSetterMethod(\ReflectionMethod $method)
    {
        $inject = $this->reader->getMethodAnnotation($method, 'Ray\Di\Di\Inject');
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
        $namedAnnotation = $this->reader->getMethodAnnotation($method, 'Ray\Di\Di\Named');
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
        $bindingAnnotation = $this->reader->getClassAnnotation(new \ReflectionClass($annotation), 'Ray\Di\Di\Qualifier');
        if (! $bindingAnnotation) {
            return null;
        }
        $named = new Named;
        $named->value = get_class($annotation);

        return $named;
    }
}
