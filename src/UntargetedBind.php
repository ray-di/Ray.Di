<?php

declare(strict_types=1);

namespace Ray\Di;

use ReflectionMethod;
use ReflectionParameter;

final class UntargetedBind
{
    public function __invoke(Container $container, ReflectionMethod $method) : void
    {
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $this->addConcreteClass($container, $parameter);
        }
    }

    private function addConcreteClass(Container $container, ReflectionParameter $parameter) : void
    {
        $class = $this->getTypeHint($parameter);
        if (class_exists($class)) {
            new Bind($container, $class);
        }
    }

    private function getTypeHint(ReflectionParameter $parameter) : string
    {
        $typeHintClass = $parameter->getClass();

        return $typeHintClass ? $typeHintClass->name : '';
    }
}
