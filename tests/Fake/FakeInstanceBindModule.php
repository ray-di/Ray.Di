<?php

namespace Ray\Di;

class FakeInstanceBindModule extends AbstractModule
{
    public function __construct()
    {
    }

    protected function configure()
    {
        $this->bind('')->annotatedWith('one')->toInstance(1);
    }
}
