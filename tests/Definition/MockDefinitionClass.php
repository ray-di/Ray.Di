<?php

namespace Ray\Di\Definition;

use Ray\Di\ForgeInterface;

use Ray\Di\Mock\DbInterface,
    Ray\Di\Mock\UserInterface,
    Ray\Di\Forge;


/**
 * @Scope("prototype")
 */
class MockDefinitionClass
{
    /**
     * @var Ray\Di\Db
     */
    public $db;

    public $msg = '';

    /**
     * Reource
     *
     * @Inject
     *
     * @var Resource
     */
    private $resource;

    /**
     * Di
     *
     * @Inject
     *
     * @var Di
     */
    private $di;

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
     * @PreDestoroy
     */
    public function onEnd()
    {
        $GLOBALS['pre_destoroy'] = '@PreDestoroy';
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
     * @Inject
     * @Named("user_db")
     *
     * @param DbInterface $db
     *
     */
    public function setUserDb(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @Inject
     * @Named("db=staege_db")
     *
     * @param DbInterface $db
     *
     */
    public function setAdminDb(DbInterface $db)
    {
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

    /**
     * @Provide
     * @Named("user")
     *
     * @return Db
     */
    public function provideDb()
    {
    }

}