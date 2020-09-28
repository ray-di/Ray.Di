<?php
namespace Ray\Di;

class FakeModuleInModuleOverride extends AbstractModule
{
    protected function configure()
    {
        $this->install(new FakeInstanceBindModuleOneTo3(new FakeInstanceBindModule));
    }
}
