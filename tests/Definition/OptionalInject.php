<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class OptionalInject
{
    /**
     * @var DbInterface
     */
    public $userDb = 'NOT_INJECTED';

    /**
     * @Inject(optional = true)
     * @Named("user_db")
     *
     * @param DbInterface $db
     *
     */
    public function setUserDb(DbInterface $db)
    {
        $this->userDb = $db;
    }

}
