<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeInstallModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new FakeInstanceBindModule());
        $this->install(new FakeInstanceBindModule2());
    }
}
