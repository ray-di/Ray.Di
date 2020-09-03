<?php
namespace Ray\Di;

class FakeInstallModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new FakeInstanceBindModule);
        $this->install(new FakeInstanceBindModule2);
    }
}
