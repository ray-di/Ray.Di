<?php

namespace Ray\Di\Mock;

use Ray\Di\Di\Inject;

class Number implements NumberInterface
{
    public $db;

    /**
     * @Inject
     */
    function __construct(DbInterface $db)
    {
        $this->db = $db;
    }
}
