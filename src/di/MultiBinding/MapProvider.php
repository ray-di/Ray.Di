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
    /** @var MultiBindings */
    private $multiBindings;

    /** @var InjectionPointInterface */
    private $ip;

    /** @var InjectorInterface */
    private $injector;

    public function __construct(InjectionPointInterface $ip, MultiBindings $multiBindings, InjectorInterface $injector)
    {
        $this->multiBindings = $multiBindings;
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

        /** @var array<string, LazyTo<object>> $keyBasedLazy */
        $keyBasedLazy = $this->multiBindings[$instance->interface];

        return new Map($keyBasedLazy, $this->injector);
    }
}
