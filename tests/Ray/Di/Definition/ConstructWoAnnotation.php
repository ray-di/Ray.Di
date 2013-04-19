<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Di\Inject;

/**
 * Constructor Injection
 *
 */
class ConstructWoAnnotation
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
