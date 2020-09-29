<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeEngineProvider implements ProviderInterface
{
    public function get()
    {
        return new FakeEngine();
    }
}
