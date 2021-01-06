<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;

use function assert;
use function call_user_func_array;
use function is_callable;

final class AssistedInjectInterceptor implements MethodInterceptor
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

    /**
     * @return mixed
     */
    public function invoke(MethodInvocation $invocation)
    {
        $this->methodInvocationProvider->set($invocation);
        $params = $invocation->getMethod()->getParameters();
        $namedArguments = $this->getNamedArguments($invocation);
        foreach ($params as $param) {
            /** @var list<ReflectionAttribute> $attributes */
            $attributes = $param->getAttributes(Inject::class);
            if (isset($attributes[0])) {
                /** @psalm-suppress MixedAssignment */
                $namedArguments[$param->getName()] = $this->getDependency($param);
            }
        }

        $callable = [$invocation->getThis(), $invocation->getMethod()->getName()];
        assert(is_callable($callable));

        return call_user_func_array($callable, $namedArguments); // @phpstan-ignore-line PHP8 named arguments
    }

    /**
     * @return array<string, mixed>
     */
    private function getNamedArguments(MethodInvocation $invocation): array
    {
        $args = $invocation->getArguments();
        $params = $invocation->getMethod()->getParameters();
        $namedParams = [];
        foreach ($params as $param) {
            $pos = $param->getPosition();
            if (isset($args[$pos])) {
                /** @psalm-suppress MixedAssignment */
                $namedParams[$param->getName()] = $args[$pos];
            }
        }

        return $namedParams;
    }

    /**
     * @return mixed
     */
    private function getDependency(ReflectionParameter $param)
    {
        $named = $this->getName($param);
        $type = $param->getType();
        assert($type instanceof ReflectionNamedType || $type === null);
        $interface = $type ? $type->getName() : '';

        return $this->injector->getInstance($interface, $named);
    }

    private function getName(ReflectionParameter $param): string
    {
        /** @var list<ReflectionAttribute> $attributes */
        $attributes = $param->getAttributes(Named::class);
        if (isset($attributes[0])) {
            $named = $attributes[0]->newInstance();
            assert($named instanceof Named);

            return $named->value;
        }

        return '';
    }
}
