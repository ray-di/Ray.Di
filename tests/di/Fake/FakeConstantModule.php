<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeConstantModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith(FakeConstant::class)->toInstance('kuma');
    }
}
