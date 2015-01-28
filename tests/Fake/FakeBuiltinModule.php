<?php

namespace Ray\Di;

class FakeBuiltinModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(FakeBuiltin::class);
    }
}
