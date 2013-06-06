<?php

namespace Ray\Di\Mock;

class RndDb implements DbInterface
{
    public $rnd;
    public $madeBy = '';

    public function __construct()
    {
        $this->rnd = rand(1, PHP_INT_MAX);
    }
}
