<?php
namespace Ray\Di;

class FakeAopInstallModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new FakeAopInterceptorModule);
        $this->install(new FakeAopInterfaceModule);
    }
}
