<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\MultiBinding\LazyInstance;
use Ray\Di\MultiBinding\LazyInteterface;
use Ray\Di\MultiBinding\LazyProvider;
use Ray\Di\MultiBinding\LazyTo;
use Ray\Di\MultiBinding\MultiBindings;

final class MultiBinder
{
    /** @var Container */
    private $container;

    /** @var MultiBindings */
    private $multiBindings;

    /** @var string */
    private $interface;

    /** @var ?string  */
    private $key;

    private function __construct(AbstractModule $module, string $interface)
    {
        $this->container = $module->getContainer();
        $this->multiBindings = $this->container->multiBindings;
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
        $this->container->multiBindings->exchangeArray([]);
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
        $bind = (new Bind($this->container, MultiBindings::class))->toInstance($this->multiBindings);
        $this->container->add($bind);
    }

    private function bind(LazyInteterface $lazy, ?string $key): void
    {
        $bindings = [];
        if ($this->multiBindings->offsetExists($this->interface)) {
            $bindings = $this->multiBindings->offsetGet($this->interface);
        }

        if ($key === null) {
            $bindings[] = $lazy;
            $this->multiBindings->offsetSet($this->interface, $bindings); // @phpstan-ignore-line

            return;
        }

        $bindings[$key] = $lazy;
        $this->multiBindings->offsetSet($this->interface, $bindings);
    }
}
