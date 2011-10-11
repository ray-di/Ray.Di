<?php

namespace Aura\Di\Modules;

use Aura\Di\ProviderInterface;

class DbProvider implements ProviderInterface
{
    /**
     * @return UserDb
     */
    public function get()
    {
        $db = new \Aura\Di\Mock\UserDb();
        return $db;
    }
}