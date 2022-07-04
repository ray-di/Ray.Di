<?php

declare(strict_types=1);

namespace Ray\Di;

use Koriym\ParamReader\ParamReaderInterface;
use Ray\Di\Di\Set;
use Ray\Di\Exception\SetNotFound;

/**
 * @implements ProviderInterface<mixed>
 * @template T of object
 */
final class ProviderSetProvider implements ProviderInterface
{
    /** @var InjectionPointInterface */
    private $ip;

    /** @var InjectorInterface */
    private $injector;

    /** @var ParamReaderInterface<T>  */
    private $reader;

    /**
     * @param ParamReaderInterface<T> $reader
     */
    public function __construct(
        InjectionPointInterface $ip,
        InjectorInterface $injector,
        ParamReaderInterface $reader
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
        $param = $this->ip->getParameter();
        /** @var ?Set<object> $set */
        $set = $this->reader->getParametrAnnotation($param, Set::class); // @phpstan-ignore-line
        if ($set === null) {
            throw new SetNotFound((string) $this->ip->getParameter());
        }

        return new class ($this->injector, $set) implements ProviderInterface
        {
            /** @var InjectorInterface  */
            private $injector;

            /** @var Set<object>  */
            private $set;

            /**
             * @param Set<object> $set
             */
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
