<?php

namespace Ray\Di\Definition;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Mock\DbInterface;

class MockInjectNamed
{
    /**
     * @var DbInterface
     */
    public $db;

    /**
     * @var DbInterface
     */
    public $userDb;

    /**
     * @Inject
     *
     * @param DbInterface $db
     */
    public function setDb(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param DbInterface $db
     *
     * @Inject
     * @named("user_db")
     */
    public function setUserDb(DbInterface $db)
    {
        $this->userDb = $db;
    }
}
