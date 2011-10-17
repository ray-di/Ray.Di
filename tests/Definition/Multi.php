<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface,
    Ray\Di\Mock\UserInterface;

/**
 * @Scope("prototype")
 */
class Multi
{
    /**
     * @var Ray\Di\Db
     */
    public $db;

    /**
     * @var Ray\Di\UserDb
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

    /**
     * @aInject
     * @Named("db=staege_db")
     *
     * @param DbInterface $db
     *
     */
    public function setAdminDb(DbInterface $db)
    {
    }

    /**
     * @aInject
     * @Named("user=admin_user,db=production_db")
     *
     * @param DbInterface $db
     *
     */
    public function setDouble(UserInterface $user, DbInterface $db)
    {
    }

    /**
     * a@Provide
     * @Named("user")
     *
     * @return Db
     */
    public function provideDb()
    {
    }

}