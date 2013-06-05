<?php

namespace Ray\Di\Mock;

use Ray\Di\ProviderInterface;

class RndDbProvider implements ProviderInterface
{
    public function get()
    {
        return new RndDb;
    }
}
