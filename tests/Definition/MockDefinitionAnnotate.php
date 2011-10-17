<?php

namespace Ray\Di\Definition;

class MockInjectNamed
{
    /**
     * @var DbInterface
     */
    public $db;

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
     * @Inject
     * @named("user_db")
     *
     * @param DbInterface $db
     */
    public function setUserDb(DbInterface $db)
    {
        $this->userDb = $db;
    }
}