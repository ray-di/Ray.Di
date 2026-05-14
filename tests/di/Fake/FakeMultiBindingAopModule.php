<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * Module with multiple bindings for same interface, where non-last has AOP.
 */
class FakeMultiBindingAopModule extends AbstractModule
{
    protected function configure(): void
    {
        // Two named bindings for same interface - 'first' (alphabetically before 'second')
        $this->bind(FakeAopInterface::class)->annotatedWith('first')->to(FakeAop::class);
        $this->bind(FakeAopInterface::class)->annotatedWith('second')->to(FakeAop::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->startsWith('returnSame'),
            [FakeDoubleInterceptor::class]
        );
    }
}
