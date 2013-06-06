<?php

namespace Ray\Di\Mock;

use Ray\Di\Di\Inject;

class RndDbConsumer
{
    public $db1;
    public $db2;

    /**
     * @Inject
     */
    public function setDb1(RndDb $db1)
    {
        $this->db1 = $db1;
    }

    /**
     * @Inject
     */
    public function setDb2(RndDb $db2)
    {
        $this->db2 = $db2;
    }
}
