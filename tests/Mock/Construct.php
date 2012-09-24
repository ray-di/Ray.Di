<?php

namespace Ray\Di\Mock;

use Ray\Di\Mock\DbInterface;

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
