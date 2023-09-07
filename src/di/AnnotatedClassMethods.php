<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\ReflectionClass;
use Ray\Aop\ReflectionMethod;
use Ray\Di\Di\InjectInterface;
use Ray\Di\Di\Named;

use const PHP_VERSION_ID;

final class AnnotatedClassMethods
{
    /** @var NameKeyVarString */
    private $nameKeyVarString;

    public function __construct()
    {
        $this->nameKeyVarString = new NameKeyVarString();
    }

    /**
     * @phpstan-param ReflectionClass<object> $class
     */
    public function getConstructorName(ReflectionClass $class): Name
    {
        $constructor = $class->getConstructor();
        if (! $constructor) {
            return new Name(Name::ANY);
        }

        if (PHP_VERSION_ID >= 80000) {
            $name = Name::withAttributes(new \ReflectionMethod($class->getName(), '__construct'));
            if ($name) {
                return $name;
            }
        }

        $named = $constructor->getAnnotation(Named::class);
        if ($named instanceof Named) {
            return new Name($named->value);
        }

        $name = ($this->nameKeyVarString)(new ReflectionMethod($class->getName(), $constructor->getName()));
        if ($name !== null) {
            return new Name($name);
        }

        return new Name(Name::ANY);
    }

    public function getSetterMethod(ReflectionMethod $method): ?SetterMethod
    {
        $inject = $method->getAnnotation(InjectInterface::class);
        if (! $inject instanceof InjectInterface) {
            return null;
        }

        $name = $this->getName($method);
        $setterMethod = new SetterMethod($method, $name);
        if ($inject->isOptional()) {
            $setterMethod->setOptional();
        }

        return $setterMethod;
    }

    private function getName(ReflectionMethod $method): Name
    {
        if (PHP_VERSION_ID >= 80000) {
            $name = Name::withAttributes($method);
            if ($name) {
                return $name;
            }
        }

        $nameValue = ($this->nameKeyVarString)($method);

        return new Name($nameValue);
    }
}
