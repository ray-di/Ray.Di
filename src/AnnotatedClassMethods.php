<?php

declare(strict_types=1);

namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\InjectInterface;
use Ray\Di\Di\Named;

final class AnnotatedClassMethods
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var NameKeyVarString
     */
    private $nameKeyVarString;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->nameKeyVarString = new NameKeyVarString($reader);
    }

    /**
     * @phpstan-param \ReflectionClass<object> $class
     */
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
        $name = ($this->nameKeyVarString)($constructor);
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
        $nameValue = ($this->nameKeyVarString)($method);
        $setterMethod = new SetterMethod($method, new Name($nameValue));
        if ($inject->isOptional()) {
            $setterMethod->setOptional();
        }

        return $setterMethod;
    }
}
