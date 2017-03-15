<?php
namespace Ray\Di;

class FakeAopDoublyInstallModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new FakeAopInterceptorModule);
        $this->install(new FakeAopInterceptorModule);
        $this->install(new FakeAopInterfaceModule);
    }
}
