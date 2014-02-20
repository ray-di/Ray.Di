<?php

namespace Ray\Di\Mock;

class RndDb implements DbInterface
{
    /**
     * @var int
     */
    public $rnd;

    /**
     * @var string
     */
    public $madeBy = '';

    public function __construct()
    {
        $this->rnd = rand(1, PHP_INT_MAX);
    }
}
