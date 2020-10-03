<?php

declare(strict_types=1);

namespace Ray\Di;

use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

use function class_exists;
use function in_array;

final class UntargetedBind
{
    public function __invoke(Container $container, ReflectionMethod $method): void
    {
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $this->addConcreteClass($container, $parameter);
        }
    }

    private function addConcreteClass(Container $container, ReflectionParameter $parameter): void
    {
        $class = $this->getType($parameter);
        if (class_exists($class)) {
            new Bind($container, $class);
        }
    }

    private function getType(ReflectionParameter $parameter): string
    {
        $type = $parameter->getType();

        return $type instanceof ReflectionNamedType && ! in_array($type->getName(), Argument::UNBOUND_TYPE, true) ? $type->getName() : '';
    }
}
