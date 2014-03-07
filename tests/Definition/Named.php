<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;

class Named
{
    /**
     * @var DbInterface
     */
    public $userDb;

    /**
     * @param DbInterface $db
     *
     * @Inject
     * @Named("user_db")
     */
    public function setUserDb(DbInterface $db)
    {
        $this->userDb = $db;
    }
}
