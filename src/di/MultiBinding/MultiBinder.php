<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use Ray\Di\AbstractModule;
use Ray\Di\Bind;
use Ray\Di\Container;

final class MultiBinder
{
    /** @var Container */
    private $container;

    /** @var array<string, array<int|string, Lazy>> */
    private $lazyCollection = [];

    /** @var string */
    private $interface;

    private function __construct(AbstractModule $module, string $interface)
    {
        $this->container = $module->getContainer();
        $this->interface = $interface;
    }

    public static function newInstance(AbstractModule $module, string $interface): self
    {
        return new self($module, $interface);
    }

    /**
     * @param class-string $class
     */
    public function add(string $class, ?string $key = null): void
    {
        $this->set($class, $key);
        $bind = (new Bind($this->container, LazyCollection::class))->toInstance(new LazyCollection($this->lazyCollection));
        $this->container->add($bind);
    }

    /**
     * @param class-string $class
     */
    public function set(string $class, ?string $key): void
    {
        if ($key === null) {
            $this->lazyCollection[$this->interface][] = new Lazy($class);

            return;
        }

        $this->lazyCollection[$this->interface][$key] = new Lazy($class);
    }
}
