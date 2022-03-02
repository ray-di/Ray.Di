<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Set;

use function assert;

final class InjectingProviderProvider implements ProviderInterface
{
    /** @var InjectionPointInterface */
    private $ip;

    /** @var InjectorInterface */
    private $injector;

    public function __construct(InjectionPointInterface $ip, InjectorInterface $injector)
    {
        $this->ip = $ip;
        $this->injector = $injector;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        $set = $this->ip->getParameter()->getAttributes(Set::class)[0];
        $instance = $set->newInstance();
        assert($instance instanceof Set);

        return new class ($this->injector, $instance) implements ProviderInterface
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
