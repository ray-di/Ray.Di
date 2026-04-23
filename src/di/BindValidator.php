<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\NullInterceptor;
use Ray\Aop\ReflectionClass;
use Ray\Di\Exception\InvalidProvider;
use Ray\Di\Exception\InvalidType;
use Ray\Di\Exception\NotFound;

use function class_exists;
use function interface_exists;
use function sprintf;

final class BindValidator
{
    public function constructor(string $interface): void
    {
        if ($interface && ! interface_exists($interface) && ! class_exists($interface)) {
            throw new NotFound($interface);
        }
    }

    /**
     * To validator
     *
     * @param class-string<T> $class
     *
     * @return ReflectionClass<T>
     *
     * @template T of object
     */
    public function to(string $interface, string $class): ReflectionClass
    {
        if (! class_exists($class)) {
            throw new NotFound($class);
        }

        if (! $this->isNullInterceptorBinding($class, $interface) && interface_exists($interface) && ! (new ReflectionClass($class))->implementsInterface($interface)) {
            throw new InvalidType(sprintf('[%s] is no implemented [%s] interface', $class, $interface));
        }

        return new ReflectionClass($class);
    }

    /**
     * toProvider validator
     *
     * @phpstan-param class-string $provider
     *
     * @psalm-return ReflectionClass
     * @phpstan-return ReflectionClass<object>
     *
     * @throws NotFound
     */
    public function toProvider(string $provider): ReflectionClass
    {
        if (! class_exists($provider)) {
            /** @psalm-suppress MixedArgument -- should be string */
            throw new NotFound($provider);
        }

        $reflectionClass = new ReflectionClass($provider);
        if (! $reflectionClass->implementsInterface(ProviderInterface::class)) {
            throw new InvalidProvider($provider);
        }

        return $reflectionClass;
    }

    private function isNullInterceptorBinding(string $class, string $interface): bool
    {
        return $class === NullInterceptor::class && interface_exists($interface) && (new ReflectionClass($interface))->implementsInterface(MethodInterceptor::class);
    }
}
