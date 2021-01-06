<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Assisted;

use const PHP_VERSION_ID;

class AssistedModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(Assisted::class),
            [AssistedInterceptor::class]
        );
        $this->bind(MethodInvocation::class)->toProvider(MethodInvocationProvider::class)->in(Scope::SINGLETON);
        $this->bind(MethodInvocationProvider::class)->in(Scope::SINGLETON);
        if (PHP_VERSION_ID >= 80000) {
            $this->install(new AssistedInjectModule());
        }
    }
}
