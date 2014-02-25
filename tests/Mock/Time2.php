<?php

namespace Ray\Di\Mock;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class Time2
{
    public $time;

    /**
     * @Inject
     * @Named("now")
     */
    public function __construct($time)
    {
        $this->time = $time;
    }
}
