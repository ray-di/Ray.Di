<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeModuleInModuleOverride extends AbstractModule
{
    protected function configure()
    {
        $this->install(new FakeInstanceBindModuleOneTo3(new FakeInstanceBindModule()));
    }
}
