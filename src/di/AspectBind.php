<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\Bind as AopBind;
use Ray\Aop\MethodInterceptor;

use function assert;
use function is_string;

final class AspectBind
{
    /** @var AopBind */
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
    public function inject(Container $container): array
    {
        $bindings = $this->bind->getBindings();
        $instantiatedBindings = [];
        foreach ($bindings as $methodName => $interceptorClassNames) {
            $interceptors = [];
            foreach ($interceptorClassNames as $interceptorClassName) {
                assert(is_string($interceptorClassName));
                /** @psalm-suppress MixedAssignment */
                $interceptor = $container->getInstance($interceptorClassName, Name::ANY);
                assert($interceptor instanceof MethodInterceptor);
                $interceptors[] = $interceptor;
            }

            $instantiatedBindings[$methodName] = $interceptors;
        }

        return $instantiatedBindings;
    }
}
