<?php

namespace Ray\Di\Modules;

use Ray\Di\ProviderInterface;

class DbProvider implements ProviderInterface
{
    /**
     * @return \Ray\Di\Mock\UserDb
     */
    public function get()
    {
        $db = new \Ray\Di\Mock\UserDb();

        return $db;
    }
}
