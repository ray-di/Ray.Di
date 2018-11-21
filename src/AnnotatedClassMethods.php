<?php

declare(strict_types=1);

namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\InjectInterface;
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

    public function getConstructorName(\ReflectionClass $class) : Name
    {
        $constructor = $class->getConstructor();
        if (! $constructor) {
            return new Name(Name::ANY);
        }
        $named = $this->reader->getMethodAnnotation($constructor, Named::class);
        if ($named instanceof Named) {
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
     * @return null|SetterMethod
     */
    public function getSetterMethod(\ReflectionMethod $method)
    {
        $inject = $this->reader->getMethodAnnotation($method, InjectInterface::class);
        if (! $inject instanceof InjectInterface) {
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
     * @return null|string
     */
    private function getNamedKeyVarString(\ReflectionMethod $method)
    {
        $keyVal = [];
        $named = $this->reader->getMethodAnnotation($method, Named::class);
        if ($named instanceof Named) {
            $keyVal[] = $named->value;
        }
        $qualifierNamed = $this->getQualifierKeyVarString($method);
        if ($qualifierNamed) {
            $keyVal[] = $qualifierNamed;
        }
        if ($keyVal !== []) {
            return implode(',', $keyVal); // var1=qualifier1,va2=qualifier2
        }
    }

    private function getQualifierKeyVarString(\ReflectionMethod $method) : string
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        $names = [];
        foreach ($annotations as $annotation) {
            /* @var $bindAnnotation object|null */
            $qualifier = $this->reader->getClassAnnotation(new \ReflectionClass($annotation), Qualifier::class);
            if ($qualifier instanceof Qualifier) {
                $value = $annotation->value ?? Name::ANY;
                $names[] = sprintf('%s=%s', $value, \get_class($annotation));
            }
        }

        return implode(',', $names);
    }
}
