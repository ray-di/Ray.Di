<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\MultiBinding\LazyCollection;
use Ray\Di\MultiBinding\LazyInstance;
use Ray\Di\MultiBinding\LazyInteterface;
use Ray\Di\MultiBinding\LazyProvider;
use Ray\Di\MultiBinding\LazyTo;

final class MultiBinder
{
    /** @var Container */
    private $container;

    /** @var LazyCollection */
    private $lazyCollection;

    /** @var string */
    private $interface;

    /** @var ?string  */
    private $key;

    private function __construct(AbstractModule $module, string $interface)
    {
        $this->container = $module->getContainer();
        $this->lazyCollection = $this->container->lazyCollection;
        $this->interface = $interface;
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
        $this->container->lazyCollection->exchangeArray([]);
        $this->key = $key;

        return $this;
    }

    /**
     * @param class-string $class
     */
    public function to(string $class): void
    {
        $this->bind(new LazyTo($class), $this->key);
        $this->register();
    }

    /**
     * @param class-string<ProviderInterface> $provider
     */
    public function toProvider(string $provider): void
    {
        $this->bind(new LazyProvider($provider), $this->key);
        $this->register();
    }

    /**
     * @param mixed $instance
     */
    public function toInstance($instance): void
    {
        $this->bind(new LazyInstance($instance), $this->key);
        $this->register();
    }

    public function register(): void
    {
        $bind = (new Bind($this->container, LazyCollection::class))->toInstance($this->lazyCollection);
        $this->container->add($bind);
    }

    /**
     * @param class-string $class
     */
    private function bind(LazyInteterface $lazy, ?string $key): void
    {
        $bindings = [];
        if ($this->lazyCollection->offsetExists($this->interface)) {
            $bindings = $this->lazyCollection->offsetGet($this->interface);
        }

        if ($key === null) {
            $bindings[] = $lazy;
            $this->lazyCollection->offsetSet($this->interface, $bindings);

            return;
        }

        $bindings[$key] = $lazy;
        $this->lazyCollection->offsetSet($this->interface, $bindings);
    }
}
