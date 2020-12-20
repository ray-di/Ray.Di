<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Matcher\ParamInjectMatcher;

class ParamInjectModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            (new ParamInjectMatcher()),
            [ParamInjectInterceptor::class]
        );
    }
}
