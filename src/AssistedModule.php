<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Assisted;

class AssistedModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(Assisted::class),
            [AssistedInterceptor::class]
        );
        $this->bind(MethodInvocation::class)->toProvider(MethodInvocationProvider::class)->in(Scope::SINGLETON);
        $this->bind(MethodInvocationProvider::class)->in(Scope::SINGLETON);
    }
}
