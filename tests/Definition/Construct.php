<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface,
    Ray\Di\Mock\UserInterface;

/**
 * Constructor Injection
 *
 */
class Construct
{
    /**
     * @var DbInterface
     */
    public $db;

    /**
     * @Inject
     *
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }
}