<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\MultiBinding\LazyInstance;
use Ray\Di\MultiBinding\LazyProvider;
use Ray\Di\MultiBinding\LazyTo;

/**
 * @psalm-import-type BindableInterface from Types
 * @psalm-import-type LazyBindingList from Types
 */
final class MultiBinder
{
    private readonly Container $container;
    private ?string $key = null;

    private function __construct(AbstractModule $module, private readonly string $interface)
    {
        $this->container = $module->getContainer();
    }

    public static function newInstance(AbstractModule $module, string $interface): self
    {
        return new self($module, $interface);
    }

    public function addBinding(?string $key = null): self
    {
        $this->key = $key;

        return $this;
    }

    public function setBinding(?string $key = null): self
    {
        $this->container->clearMultiBindings($this->interface);
        $this->key = $key;

        return $this;
    }

    /**
     * @param class-string $class
     */
    public function to(string $class): void
    {
        $this->container->addMultiBinding($this->interface, $this->key, new LazyTo($class));
    }

    /**
     * @param class-string<ProviderInterface<T>> $provider
     *
     * @template T of mixed
     */
    public function toProvider(string $provider): void
    {
        $this->container->addMultiBinding($this->interface, $this->key, new LazyProvider($provider));
    }

    /**
     * @param mixed $instance
     */
    public function toInstance($instance): void
    {
        $this->container->addMultiBinding($this->interface, $this->key, new LazyInstance($instance));
    }
}
