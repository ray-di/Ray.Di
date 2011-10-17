<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface,
    Ray\Di\Mock\UserInterface;

/**
 * Setter Injection
 *
 */
class Basic
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
    public function setDb(DbInterface $db)
    {
        $this->db = $db;
    }
}