<?php

namespace Ray\Di\Mock;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;

/**
 * @Scope("singleton")
 */
class SingletonNumber implements NumberInterface
{
    public $db;

    /**
     * @Inject
     */
    public function __construct(SingletonDbInterface $db)
    {
        $this->db = $db;
    }
}
