<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Mock\UserDb;
use Ray\Di\Di\Inject;

class InjectOnce implements BasicInterface
{
    public $count = 0;

    /**
     * @Inject
     *
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
            $this->count++;
    }
}
