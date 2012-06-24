<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\Db;
use Ray\Di\Di\Inject;

/**
 * Class Injection
 *
 */
class ClassHint
{
    /**
     * @var Db
     */
    public $db;

    /**
     * @Inject
     *
     * @param Db $db
     */
    public function setDb(Db $db)
    {
        $this->db = $db;
    }
}
