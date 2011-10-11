<?php

namespace Aura\Di\Definition;

use Aura\Di\Mock\DbInterface,
    Aura\Di\Mock\UserInterface;

class Named
{
    /**
     * @var DbInterface
     */
    public $userDb;


    /**
     * @Inject
     * @Named("user_db")
     *
     * @param DbInterface $db
     *
     */
    public function setUserDb(DbInterface $db)
    {
        $this->userDb = $db;
    }
}