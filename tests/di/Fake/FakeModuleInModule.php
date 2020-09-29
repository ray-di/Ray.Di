<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeModuleInModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new FakeInstanceBindModule2(new FakeInstanceBindModule()));
    }
}
