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
