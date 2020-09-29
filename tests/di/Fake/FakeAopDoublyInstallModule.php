<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeAopDoublyInstallModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new FakeAopInterceptorModule());
        $this->install(new FakeAopInterceptorModule());
        $this->install(new FakeAopInterfaceModule());
    }
}
