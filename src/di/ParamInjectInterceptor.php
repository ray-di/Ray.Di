<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use ReflectionParameter;

use function assert;
use function call_user_func_array;

final class ParamInjectInterceptor implements MethodInterceptor
{
    /** @var InjectorInterface */
    private $injector;

    /** @var MethodInvocationProvider */
    private $methodInvocationProvider;

    public function __construct(InjectorInterface $injector, MethodInvocationProvider $methodInvocationProvider)
    {
        $this->injector = $injector;
        $this->methodInvocationProvider = $methodInvocationProvider;
    }

    public function invoke(MethodInvocation $invocation)
    {
        $params = $invocation->getMethod()->getParameters();
        $namedArguments = $invocation->getNamedArguments()->getArrayCopy();
        $injectedParams = [];
        foreach ($params as $param) {
            $attributes = $param->getAttributes(Inject::class);
            if (isset($attributes[0])) {
                $injectedParams[$param->getName()] = $this->getDependency($param);
            }
        }

        $args = $injectedParams + $params;

        return call_user_func_array([$invocation->getThis(), $invocation->getMethod()->getName()], $args);
    }

    private function getDependencies(array $params)
    {
        $params = [];
        foreach ($params as $param) {
            $dependecy = $this->getDependency($param);
        }
    }

    private function getDependency(ReflectionParameter $param)
    {
        $named = $this->getName($param);
        $interface = $param->getType()->getName();

        return $this->injector->getInstance($interface, $named);
    }

    private function getName(ReflectionParameter $param): string
    {
        $attributes = $param->getAttributes(Named::class);
        if (isset($attributes[0])) {
            $named = $attributes[0]->newInstance();
            assert($named instanceof Named);

            return $named->value;
        }

        return '';
    }
}
