<?php

namespace Ray\Di\Mock;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class RndDbNamedConsumer
{
    /**
     * @var RndDb
     */
    public $db1;

    /**
     * @Inject
     * @Named("db")
     */
    public function setDb1(DbInterface $db1)
    {
        $this->db1 = $db1;
    }
}
