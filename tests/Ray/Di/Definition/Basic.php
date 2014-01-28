<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Di\Inject;

class Basic implements BasicInterface
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
    public function setDb(DbInterface $db)
    {
        $this->db = $db;
    }
}
