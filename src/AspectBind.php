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

    /**
     * @param AopBind $bind
     */
    public function __construct(AopBind $bind)
    {
        $this->bind = $bind;
    }

    /**
     * @param Container $container
     */
    public function inject(Container $container)
    {
        foreach ($this->bind as &$interceptors) {
            foreach ($interceptors as &$method) {
                foreach ($method as &$interceptor) {
                    $interceptor = $container->getInstance($interceptor, Name::ANY);
                }
            }
        }

        return $this->bind;
    }
}
