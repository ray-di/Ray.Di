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

    /** @var array<Lazy> */
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

    public function add(string $key, string $class): void
    {
        $this->lazyCollection[$this->interface][$key] = new Lazy($class);
        $bind = (new Bind($this->container, LazyCollection::class))->toInstance(new LazyCollection($this->lazyCollection));
        $this->container->add($bind);
    }
}
