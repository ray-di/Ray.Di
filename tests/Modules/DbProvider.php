<?php

namespace Ray\Di\Modules;

use Ray\Di\ProviderInterface;

class DbProvider implements ProviderInterface
{
    /**
     * @return UserDb
     */
    public function get()
    {
        $db = new \Ray\Di\Mock\UserDb();

        return $db;
    }
}
