<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
        $bindings = $this->bind->getBindings();
        foreach ($bindings as &$interceptors) {
            foreach ($interceptors as &$interceptor) {
                $interceptor = $container->getInstance($interceptor, Name::ANY);
            }
        }

        return $bindings;
    }
}
