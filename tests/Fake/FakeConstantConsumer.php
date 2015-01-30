<?php

namespace Ray\Di;

class FakeConstantConsumer
{
    public $constant;

    /**
     * @FakeConstant
     */
    public function __construct($constant)
    {
        $this->constant = $constant;
    }
}
