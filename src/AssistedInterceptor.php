<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Aop\ReflectionMethod;
use Ray\Di\Di\Assisted;
use Ray\Di\Di\Named;

final class AssistedInterceptor implements MethodInterceptor
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @var MethodInvocationProvider
     */
    private $methodInvocationProvider;

    public function __construct(InjectorInterface $injector, MethodInvocationProvider $methodInvocationProvider)
    {
        $this->injector = $injector;
        $this->methodInvocationProvider = $methodInvocationProvider;
    }

    /**
     * Intercepts any method and injects instances of the missing arguments
     * when they are type hinted
     *
     * @return object
     */
    public function invoke(MethodInvocation $invocation)
    {
        /** @var ReflectionMethod $method */
        $method = $invocation->getMethod();
        $assisted = $method->getAnnotation(Assisted::class);
        /* @var \Ray\Di\Di\Assisted $assisted */
        $parameters = $method->getParameters();
        $arguments = $invocation->getArguments()->getArrayCopy();
        if ($assisted instanceof Assisted) {
            $this->methodInvocationProvider->set($invocation);
            $arguments = $this->injectAssistedParameters($method, $assisted, $parameters, $arguments);
        }
        $invocation->getArguments()->exchangeArray($arguments);

        return $invocation->proceed();
    }

    /**
     * @internal param int $cntArgs
     */
    public function injectAssistedParameters(ReflectionMethod $method, Assisted $assisted, array $parameters, array $arguments) : array
    {
        foreach ($parameters as $parameter) {
            if (! \in_array($parameter->getName(), $assisted->values, true)) {
                continue;
            }
            /* @var $parameter \ReflectionParameter */
            $hint = $parameter->getClass();
            $interface = $hint ? $hint->getName() : '';
            $name = $this->getName($method, $parameter);
            $pos = $parameter->getPosition();
            $arguments[$pos] = $this->injector->getInstance($interface, $name);
        }

        return $arguments;
    }

    /**
     * Return dependency "name"
     */
    private function getName(ReflectionMethod $method, \ReflectionParameter $parameter) : string
    {
        $named = $method->getAnnotation(Named::class);
        if (! $named instanceof Named) {
            return Name::ANY;
        }
        parse_str($named->value, $names);
        $paramName = $parameter->getName();
        if (isset($names[$paramName])) {
            return $names[$paramName];
        }

        return Name::ANY;
    }
}
