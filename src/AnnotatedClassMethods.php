<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\Named;
use Ray\Di\Di\Qualifier;

final class AnnotatedClassMethods
{
    /**
     * @var Reader
     */
    private $reader;

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
            /* @var $named Named */
            return new Name($named->value);
        }
        $name = $this->getNamedKeyVarString($constructor);
        if ($name !== null) {
            return new Name($name);
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
        $inject = $this->reader->getMethodAnnotation($method, 'Ray\Di\Di\InjectInterface');

        /* @var $inject \Ray\Di\Di\Inject */
        if (! $inject) {
            return null;
        }
        $nameValue = $this->getNamedKeyVarString($method);
        $setterMethod = new SetterMethod($method, new Name($nameValue));
        if ($inject->isOptional()) {
            $setterMethod->setOptional();
        }

        return $setterMethod;
    }

    /**
     * @param \ReflectionMethod $method
     *
     * @return string|null
     */
    private function getNamedKeyVarString(\ReflectionMethod $method)
    {
        $keyVal = [];
        /* @var $named Named */
        $named = $this->reader->getMethodAnnotation($method, 'Ray\Di\Di\Named');
        if ($named) {
            $keyVal[] = $named->value;
        }
        $qualifierNamed = $this->getQualifierKeyVarString($method);
        if ($qualifierNamed) {
            $keyVal[] = $qualifierNamed;
        }
        if ($keyVal) {
            return implode(',', $keyVal); // var1=qualifier1,va2=qualifier2
        }

        return null;
    }

    /**
     * @param \ReflectionMethod $method
     *
     * @return string
     */
    private function getQualifierKeyVarString(\ReflectionMethod $method)
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        $names = [];
        foreach ($annotations as $annotation) {
            /* @var $bindAnnotation object|null */
            $qualifier = $this->reader->getClassAnnotation(new \ReflectionClass($annotation), 'Ray\Di\Di\Qualifier');
            if ($qualifier instanceof Qualifier) {
                $value = isset($annotation->value) ? $annotation->value : Name::ANY;
                $names[] = sprintf('%s=%s', $value, get_class($annotation));
            }
        }

        return implode(',', $names);
    }
}
