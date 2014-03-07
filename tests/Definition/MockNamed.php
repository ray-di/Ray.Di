<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class MockNamed
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
