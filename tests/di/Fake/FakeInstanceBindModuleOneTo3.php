<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeInstanceBindModuleOneTo3 extends AbstractModule
{
    protected function configure()
    {
        $this->bind('')->annotatedWith('one')->toInstance(3);
    }
}
