<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use Ray\Di\Di\Set;
use Ray\Di\InjectionPointInterface;
use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface;
use ReflectionAttribute;

final class MapProvider implements ProviderInterface
{
    /** @var LazyCollection */
    private $lazyCollection;

    /** @var InjectionPointInterface */
    private $ip;

    /** @var InjectorInterface */
    private $injector;

    public function __construct(InjectionPointInterface $ip, LazyCollection $lazyCollection, InjectorInterface $injector)
    {
        $this->lazyCollection = $lazyCollection;
        $this->ip = $ip;
        $this->injector = $injector;
    }

    public function get(): Map
    {
        /** @var array<ReflectionAttribute> $attributes */
        $attributes = $this->ip->getParameter()->getAttributes(Set::class);
        $set = $attributes[0];
        /** @var Set $instance */
        $instance = $set->newInstance();

        /** @var array<string, Lazy> $keyBasedLazy */
        $keyBasedLazy = $this->lazyCollection[$instance->interface];

        return new Map($keyBasedLazy, $this->injector);
    }
}
