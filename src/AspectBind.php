<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\Bind as AopBind;

final class AspectBind
{
    /**
     * @var AopBind
     */
    private $bind;

    public function __construct(AopBind $bind)
    {
        $this->bind = $bind;
    }

    /**
     * Instantiate interceptors
     */
    public function inject(Container $container) : array
    {
        /** @var array<array<class-string>> $bindings */
        $bindings = $this->bind->getBindings();
        foreach ($bindings as &$interceptors) {
            foreach ($interceptors as &$interceptor) {
                $interceptor = $container->getInstance($interceptor, Name::ANY);
            }
        }

        return $bindings;
    }
}
