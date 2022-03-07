<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Set;
use Ray\Di\Exception\SetNotFound;

final class ProviderSetProvider implements ProviderInterface
{
    /** @var InjectionPointInterface */
    private $ip;

    /** @var InjectorInterface */
    private $injector;

    /** @var ParameterReader  */
    private $reader;

    public function __construct(
        InjectionPointInterface $ip,
        InjectorInterface $injector,
        ParameterReader $reader
    ) {
        $this->ip = $ip;
        $this->injector = $injector;
        $this->reader = $reader;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        /** @var ?Set $set */
        $set = $this->reader->getParametrAnnotation($this->ip->getParameter(), Set::class);
        if ($set === null) {
            throw new SetNotFound((string) $this->ip->getParameter());
        }

        return new class ($this->injector, $set) implements ProviderInterface
        {
            /** @var InjectorInterface  */
            private $injector;

            /** @var Set  */
            private $set;

            public function __construct(InjectorInterface $injector, Set $set)
            {
                $this->injector = $injector;
                $this->set = $set;
            }

            /**
             * @return mixed
             */
            public function get()
            {
                return $this->injector->getInstance($this->set->interface, $this->set->name);
            }
        };
    }
}
