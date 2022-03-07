<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use Koriym\ParamReader\ParamReaderInterface;
use Ray\Di\Di\Set;
use Ray\Di\Exception\SetNotFound;
use Ray\Di\InjectionPointInterface;
use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface;

final class MapProvider implements ProviderInterface
{
    /** @var MultiBindings */
    private $multiBindings;

    /** @var InjectionPointInterface */
    private $ip;

    /** @var InjectorInterface */
    private $injector;

    /** @var ParamReaderInterface  */
    private $reader;

    public function __construct(
        InjectionPointInterface $ip,
        MultiBindings $multiBindings,
        InjectorInterface $injector,
        ParamReaderInterface $reader
    ) {
        $this->multiBindings = $multiBindings;
        $this->ip = $ip;
        $this->injector = $injector;
        $this->reader = $reader;
    }

    public function get(): Map
    {
        /** @var ?Set $set */
        $set = $this->reader->getParametrAnnotation($this->ip->getParameter(), Set::class);
        if ($set === null) {
            throw new SetNotFound((string) $this->ip->getParameter());
        }

        /** @var array<string, LazyTo<object>> $lazies */
        $lazies = $this->multiBindings[$set->interface];

        return new Map($lazies, $this->injector);
    }
}
