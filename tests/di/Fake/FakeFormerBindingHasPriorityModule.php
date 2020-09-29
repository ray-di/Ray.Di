<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeFormerBindingHasPriorityModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new FakeInstanceBindModule());
        $this->install(new FakeInstanceBindModuleOneTo3());
    }
}
