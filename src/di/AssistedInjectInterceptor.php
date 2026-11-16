<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Assisted;
use Ray\Di\Di\Inject;
use Ray\Di\Di\InjectInterface;
use Ray\Di\Di\Named;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;

use function array_is_list;
use function array_key_exists;
use function array_slice;
use function assert;
use function count;
use function in_array;

/**
 * @psalm-import-type NamedArguments from Types
 * @psalm-import-type InjectableValue from Types
 */

/**
 * Assisted injection interceptor for #[Inject] attributed parameter
 *
 * @psalm-import-type NamedArguments from Types
 */
final class AssistedInjectInterceptor implements MethodInterceptor
{
    public function __construct(private InjectorInterface $injector, private MethodInvocationProvider $methodInvocationProvider)
    {
    }

    /** @return mixed */
    public function invoke(MethodInvocation $invocation)
    {
        $this->methodInvocationProvider->set($invocation);
        try {
            $params = $invocation->getMethod()->getParameters();
            /** @var array<array-key, mixed> $args */
            $args = $invocation->getArguments()->getArrayCopy();
            $namedArguments = $this->getNamedArguments($params, $args);
            foreach ($params as $param) {
                /** @var list<ReflectionAttribute> $inject */
                $inject = $param->getAttributes(InjectInterface::class, ReflectionAttribute::IS_INSTANCEOF); // @phpstan-ignore-line
                /** @var list<ReflectionAttribute> $assisted */
                $assisted = $param->getAttributes(Assisted::class);
                // The container decides an injected parameter: a value the caller passed is replaced.
                if (isset($assisted[0]) || isset($inject[0])) {
                    /** @psalm-suppress MixedAssignment */
                    $namedArguments[$param->getName()] = $this->getDependency($param);
                }
            }

            $invocation->getArguments()->exchangeArray($this->withTrailingArguments($params, $args, $namedArguments));

            return $invocation->proceed();
        } finally {
            $this->methodInvocationProvider->pop();
        }
    }

    /**
     * @param list<ReflectionParameter> $params
     * @param array<array-key, mixed>   $args
     *
     * @return array<string, mixed>
     */
    private function getNamedArguments(array $params, array $args): array
    {
        $namedParams = [];
        foreach ($params as $param) {
            if ($param->isVariadic()) {
                continue;
            }

            $pos = $param->getPosition();
            if (array_key_exists($pos, $args)) {
                /** @psalm-suppress MixedAssignment */
                $namedParams[$param->getName()] = $args[$pos];
                continue;
            }

            // An interceptor earlier in the chain may already have re-keyed the arguments by name.
            if (array_key_exists($param->getName(), $args)) {
                /** @psalm-suppress MixedAssignment */
                $namedParams[$param->getName()] = $args[$param->getName()];
            }
        }

        return $namedParams;
    }

    /**
     * Return the arguments by position when the caller passed more than the declared parameters
     *
     * A variadic tail or surplus arguments cannot follow named arguments. They exist only when every
     * declared position is filled, so the whole call can be made by position.
     *
     * @param list<ReflectionParameter> $params
     * @param array<array-key, mixed>   $args
     * @param array<string, mixed>      $namedArguments
     *
     * @return array<array-key, mixed>
     */
    private function withTrailingArguments(array $params, array $args, array $namedArguments): array
    {
        $declared = [];
        foreach ($params as $param) {
            if (! $param->isVariadic()) {
                $declared[] = $param->getName();
            }
        }

        if (! array_is_list($args) || count($args) <= count($declared)) {
            return $namedArguments;
        }

        $positional = [];
        foreach ($declared as $name) {
            /** @psalm-suppress MixedAssignment */
            $positional[] = $namedArguments[$name];
        }

        return [...$positional, ...array_slice($args, count($declared))];
    }

    /** @return mixed */
    private function getDependency(ReflectionParameter $param)
    {
        $named = (string) $this->getName($param);
        $type = $param->getType();
        assert($type instanceof ReflectionNamedType || $type === null);
        $typeName = $type instanceof ReflectionNamedType ? $type->getName() : '';
        $interface = in_array($typeName, Argument::UNBOUND_TYPE) ? '' : $typeName;

        /** @var class-string $interface */
        return $this->injector->getInstance($interface, $named);
    }

    private function getName(ReflectionParameter $param): ?string
    {
        /** @var list<ReflectionAttribute> $nameds */
        $nameds = $param->getAttributes(Named::class);
        if (isset($nameds[0])) {
            $named = $nameds[0]->newInstance();
            assert($named instanceof Named);

            return $named->value;
        }

        if ($param->getAttributes(Inject::class)) {
            return null;
        }

        return $this->getCustomInject($param);
    }

    /** @return ?class-string */
    private function getCustomInject(ReflectionParameter $param): ?string
    {
        /** @var list<ReflectionAttribute> $injects */
        $injects = $param->getAttributes(InjectInterface::class, ReflectionAttribute::IS_INSTANCEOF);
        if (! $injects) {
            return null;
        }

        $inject = $injects[0]->newInstance();
        assert($inject instanceof InjectInterface);

        return $inject::class;
    }
}
