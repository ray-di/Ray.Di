<?php

namespace Ray\Di\Mock;

class NoAnnotation
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
