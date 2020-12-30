<?php

declare(strict_types=1);

namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\InjectInterface;
use Ray\Di\Di\Named;
use Reflection;
use ReflectionClass;
use ReflectionMethod;

use function version_compare;

use const PHP_VERSION;
use const PHP_VERSION_ID;

final class AnnotatedClassMethods
{
    /** @var Reader */
    private $reader;

    /** @var NameKeyVarString */
    private $nameKeyVarString;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->nameKeyVarString = new NameKeyVarString($reader);
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
            $name = (new Name())->createFromAttributes(new ReflectionMethod($class->getName(), '__construct'));
            if ($name) {
                return $name;
            }
        }

        $named = $this->reader->getMethodAnnotation($constructor, Named::class);
        if ($named instanceof Named) {
            /** @var Named $named */
            return new Name($named->value);
        }

        $name = ($this->nameKeyVarString)($constructor);
        if ($name !== null) {
            return new Name($name);
        }

        return new Name(Name::ANY);
    }

    /**
     * @return ?SetterMethod
     */
    public function getSetterMethod(ReflectionMethod $method)
    {
        $inject = $this->reader->getMethodAnnotation($method, InjectInterface::class);
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
            $name = (new Name())->createFromAttributes($method);
            if ($name) {
                return $name;
            }
        }

        $nameValue = ($this->nameKeyVarString)($method);

        return new Name($nameValue);
    }
}
