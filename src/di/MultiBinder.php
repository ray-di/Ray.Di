<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Exception\Unbound;
use Ray\Di\MultiBinding\LazyCollection;
use Ray\Di\MultiBinding\LazyInstance;
use Ray\Di\MultiBinding\LazyInteterface;
use Ray\Di\MultiBinding\LazyProvider;
use Ray\Di\MultiBinding\LazyTo;

final class MultiBinder
{
    /** @var Container */
    private $container;

    /** @var array<string, array<int|string, LazyInteterface>> */
    private $lazyCollection = [];

    /** @var string */
    private $interface;

    /** @var ?string  */
    private $key;

    private function __construct(AbstractModule $module, string $interface)
    {
        $this->container = $module->getContainer();
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
        unset($this->lazyCollection[$this->interface]);
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
        try {
            $lazyCollection = $this->container->getInstance(LazyCollection::class);
            $this->lazyCollection += $lazyCollection->getArrayCopy();
        } catch (Unbound $e) {
        }

        $lazyCollection = new LazyCollection($this->lazyCollection);
        $bind = (new Bind($this->container, LazyCollection::class))->toInstance($lazyCollection);
        $this->container->add($bind);
    }

    /**
     * @param class-string $class
     */
    private function bind(LazyInteterface $lazy, ?string $key): void
    {
        if ($key === null) {
            $this->lazyCollection[$this->interface][] = $lazy;

            return;
        }

        $this->lazyCollection[$this->interface][$key] = $lazy;
    }
}
