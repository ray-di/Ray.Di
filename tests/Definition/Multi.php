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
     * @param \Ray\Di\Mock\DbInterface $userDb
     */
    public function setUserDb(DbInterface $userDb)
    {
        $this->userDb = $userDb;
    }

    /**
     * @Inject
     * @Named("adminDb=admin_db")
     *
     * @param \Ray\Di\Mock\DbInterface $adminDb
     */
    public function setAdminDb(DbInterface $adminDb)
    {
        $this->adminDb = $adminDb;
    }

    /**
     * @Inject
     * @Named("user=admin_user,db=production_db")
     *
     * @param \Ray\Di\Mock\UserInterface $user
     * @param \Ray\Di\Mock\DbInterface   $db
     */
    public function setDouble(UserInterface $user, DbInterface $db)
    {
    }

}
