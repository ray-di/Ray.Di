<?php

namespace Ray\Di\Definition;

use Aura\Di\ForgeInterface;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Mock\UserInterface;
use Ray\Di\Forge;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\Scope;
use Ray\Di\Di\PreDestroy;
use Ray\Di\Di\PostConstruct;

/**
 * @Scope("prototype")
 */
class MockDefinitionClass
{
    /**
     * @var DbInterface
     */
    public $db;

    public $msg = '';

    /**
     * Di
     *
     * @Inject
     *
     * @var Forge
     */
    private $forge;

    /**
     * @Inject
     * @Named("id=usr_id");
     */
    public function __construct(ForgeInterface $forge = null, $id)
    {
        $this->forge = $forge;
    }

    /**
     * Init
     *
     * @PostConstruct
     */
    public function onInit()
    {
        $this->msg = '@PostConstruct';
    }

    /**
     * @PreDestroy
     */
    public function onEnd()
    {
        $GLOBALS['pre_destroy'] = '@PreDestroy';
    }

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
     * @Named("user_db")
     */
    public function setUserDb(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param DbInterface $db
     *
     * @Inject
     * @Named("db=stage_db")
     */
    public function setAdminDb(DbInterface $db)
    {
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

    /**
     * @Named("user")
     */
    public function provideDb()
    {
    }
}
