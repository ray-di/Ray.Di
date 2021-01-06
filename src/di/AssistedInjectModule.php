<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Matcher\AssistedInjectMatcher;

/**
 * Assisted module for php8 attributes
 */
class AssistedInjectModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            (new AssistedInjectMatcher()),
            [AssistedInjectInterceptor::class]
        );
    }
}
