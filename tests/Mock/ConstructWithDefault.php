<?php

namespace Ray\Di\Mock;

use Ray\Di\Mock\DbInterface;

class DefaultDB{}

/**
 * Constructor Injection
 *
 */
class ConstructWithDefault
{
    /**
     * @var DbInterface
     */
    public $db;

    /**
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db = null)
    {
        if (is_null($db)) {
            $db = new DefaultDB;
        }
        $this->db = $db;
    }
}
