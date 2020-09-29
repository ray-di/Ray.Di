<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeAopGrapher implements FakeAopInterface
{
    public $a;

    public function __construct($a)
    {
        $this->a = $a;
    }

    public function returnSame($a)
    {
        return $a;
    }
}
