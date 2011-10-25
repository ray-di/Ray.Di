<?php

namespace Ray\Di\Mock;

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
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }
}