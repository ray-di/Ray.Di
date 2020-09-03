<?php
namespace Ray\Di;

class FakeOverrideInstallModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new FakeInstanceBindModule);
        $this->override(new FakeInstanceBindModuleOneTo3);
    }
}
