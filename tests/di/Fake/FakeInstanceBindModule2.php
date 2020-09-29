<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeInstanceBindModule2 extends AbstractModule
{
    protected function configure()
    {
        $this->bind('')->annotatedWith('two')->toInstance(2);
    }
}
