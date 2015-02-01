<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
     * @param Container $container
     *
     * @return array
     */
    public function inject(Container $container)
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
