<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Di\Inject;

class Construct
{
    /**
     * @var DbInterface
     */
    public $db;

    /**
     * @param DbInterface $db
     *
     * @Inject
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }
}
