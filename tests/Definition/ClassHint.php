<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\Db;
use Ray\Di\Di\Inject;

class ClassHint
{
    /**
     * @var Db
     */
    public $db;

    /**
     * Concrete class type hinting
     *
     * @param Db $db
     *
     * @Inject
     */
    public function setDb(Db $db)
    {
        $this->db = $db;
    }
}
