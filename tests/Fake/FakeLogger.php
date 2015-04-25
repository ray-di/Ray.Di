<?php

namespace Ray\Di;

class FakeLogger implements FakeLoggerInterface
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
