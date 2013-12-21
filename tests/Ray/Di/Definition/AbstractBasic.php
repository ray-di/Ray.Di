<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Mock\AbstractDb;
use Ray\Di\Di\Inject;

class AbstractBasic
{
    /**
     * @var DbInterface
     */
    public $db;

    /**
     * @param AbstractDb $db
     *
     * @Inject
     */
    public function setDb(AbstractDb $db)
    {
        $this->db = $db;
    }
}
