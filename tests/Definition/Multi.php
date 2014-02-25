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
     * @var DbInterface
     */
    public $db;

    /**
     * @var DbInterface
     */
    public $userDb;

    /**
     * @var DbInterface
     */
    public $adminDb;
    /**
     * @param DbInterface $db
     *
     * @Inject
     */
    public function setDb(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param \Ray\Di\Mock\DbInterface $userDb
     *
     * @Inject
     * @Named("user_db")
     */
    public function setUserDb(DbInterface $userDb)
    {
        $this->userDb = $userDb;
    }

    /**
     * @param \Ray\Di\Mock\DbInterface $adminDb
     *
     * @Inject
     * @Named("adminDb=admin_db")
     */
    public function setAdminDb(DbInterface $adminDb)
    {
        $this->adminDb = $adminDb;
    }

    /**
     * @param \Ray\Di\Mock\UserInterface $user
     * @param \Ray\Di\Mock\DbInterface   $db
     *
     * @Inject
     * @Named("user=admin_user,db=production_db")
     */
    public function setDouble(UserInterface $user, DbInterface $db)
    {
    }

}
