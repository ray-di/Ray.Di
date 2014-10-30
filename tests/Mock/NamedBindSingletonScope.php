<?php

namespace Ray\Di\Mock;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class NamedBindSingletonScope
{
    public $firstDb;

    public $secondDb;

    /**
     * @Inject
     * @Named("first")
     */
    public function setFirstDb(DbInterface $db)
    {
        $this->firstDb = $db;
    }

    /**
     * @Inject
     * @Named("second")
     */
    public function setSecondDb(DbInterface $db)
    {
        $this->secondDb = $db;
    }
}