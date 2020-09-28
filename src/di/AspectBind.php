<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\Bind as AopBind;
use Ray\Aop\MethodInterceptor;

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
     *
     * @return array<string, array<MethodInterceptor>>
     */
    public function inject(Container $container) : array
    {
        $bindings = $this->bind->getBindings();
        $instanciatedBindings = [];
        foreach ($bindings as $methodName => $interceptorClassNames) {
            $interceptors = [];
            foreach ($interceptorClassNames as &$interceptorClassName) {
                assert(is_string($interceptorClassName));
                /** @var MethodInterceptor $interceptor */
                $interceptor = $container->getInstance($interceptorClassName, Name::ANY);
                $interceptors[] = $interceptor;
            }
            $instanciatedBindings[$methodName] = $interceptors;
        }

        return $instanciatedBindings;
    }
}
