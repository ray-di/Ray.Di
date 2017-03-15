<?php
namespace Ray\Di;

class FakeInstanceBindModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('')->annotatedWith('one')->toInstance(1);
    }
}
