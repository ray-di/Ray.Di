<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Annotation\FakeInjectOne;

class FakeInstanceBindModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('')->annotatedWith('one')->toInstance(1);
        $this->bind('')->annotatedWith(FakeInjectOne::class)->toInstance(1);
    }
}
