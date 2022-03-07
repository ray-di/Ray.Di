<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use LogicException;
use Ray\Di\Di\Set;
use Ray\Di\InjectionPointInterface;
use Ray\Di\InjectorInterface;
use Ray\Di\ParameterAttributeReader;
use Ray\Di\ProviderInterface;

final class MapProvider implements ProviderInterface
{
    /** @var MultiBindings */
    private $multiBindings;

    /** @var InjectionPointInterface */
    private $ip;

    /** @var InjectorInterface */
    private $injector;

    /** @var ParameterAttributeReader  */
    private $reader;

    public function __construct(
        InjectionPointInterface $ip,
        MultiBindings $multiBindings,
        InjectorInterface $injector,
        ParameterAttributeReader $reader
    ) {
        $this->multiBindings = $multiBindings;
        $this->ip = $ip;
        $this->injector = $injector;
        $this->reader = $reader;
    }

    public function get(): Map
    {
        /** @var ?Set $set */
        $set = $this->reader->get($this->ip->getParameter(), Set::class);
        if ($set === null) {
            throw new LogicException();
        }

        /** @var array<string, LazyTo<object>> $keyBasedLazy */
        $keyBasedLazy = $this->multiBindings[$set->interface];

        return new Map($keyBasedLazy, $this->injector);
    }
}
