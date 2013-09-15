<?php

namespace Ray\Di\Modules;

use Ray\Di\Mock\UserDb;
use Ray\Di\ProviderInterface;

class DbProvider implements ProviderInterface
{
    /**
     * @return UserDb
     */
    public function get()
    {
        $db = new UserDb();

        return $db;
    }
}
