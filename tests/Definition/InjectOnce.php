<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;
use Ray\Di\Di\Inject;

class InjectOnce implements BasicInterface
{
    public $count = 0;

    /**
     * @param DbInterface $db
     *
     * @Inject
     */
    public function __construct(DbInterface $db)
    {
            $this->count++;
    }
}
