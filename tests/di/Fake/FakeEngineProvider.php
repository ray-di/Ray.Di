<?php
namespace Ray\Di;

class FakeEngineProvider implements ProviderInterface
{
    public function get()
    {
        return new FakeEngine;
    }
}
