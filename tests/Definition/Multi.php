<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Mock\UserInterface;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;
use Ray\Di\Di\Named;

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
    public function setUserDb(DbInterface $userDb)
    {
        $this->userDb = $userDb;
    }

    /**
     * @Inject
     * @Named("adminDb=admin_db")
     *
     * @param DbInterface $db
     *
     */
    public function setAdminDb(DbInterface $adminDb)
    {
        $this->adminDb = $adminDb;
    }

    /**
     * @Inject
     * @Named("user=admin_user,db=production_db")
     *
     * @param DbInterface $db
     *
     */
    public function setDouble(UserInterface $user, DbInterface $db)
    {
    }

}
