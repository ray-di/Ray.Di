<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Mock\AbstractDb;
use Ray\Di\Di\Inject;
/**
 * Setter Injection
 *
 */
class AbstractBasic
{
    /**
     * @var DbInterface
     */
    public $db;

    /**
     * @Inject
     *
     * @param AbstractDb $db
     */
    public function setDb(AbstractDb $db)
    {
        $this->db = $db;
    }
}
