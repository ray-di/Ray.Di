<?php

declare(strict_types=1);

namespace Ray\Di;

interface BindInterface
{
    public function __toString(): string;

    public function annotatedWith(string $name): self;

    /**
     * @param class-string $class
     */
    public function to(string $class): self;

    /**
     * @param class-string<T> $class
     *
     * @template T of object
     */
    public function toConstructor(string $class, string $name, ?InjectionPoints $injectionPoints = null, ?string $postConstruct = null): self;

    /**
     * @phpstan-param class-string $provider
     */
    public function toProvider(string $provider, string $context = ''): self;

    public function toInstance(object $instance): self;

    public function toNull(): self;

    public function in(string $scope): self;

    public function getBound(): DependencyInterface;

    public function setBound(DependencyInterface $bound): void;
}
