<?php

declare(strict_types=1);

use Ray\Di\Di\PostConstruct;

class Db implements DbInterface
{
    public function __construct($dsn, $username, $password)
    {
    }

    #[PostConstruct]
    public function init()
    {
    }
}
