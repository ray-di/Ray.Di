<?php

namespace Ray\Di\Mock;

use Ray\Di\ProviderInterface;

class RndDbProvider implements ProviderInterface
{
    public function get()
    {
        $db = new RndDb;
        $db->madeBy = __METHOD__;

        return $db;
    }
}
