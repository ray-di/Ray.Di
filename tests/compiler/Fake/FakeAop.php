<?php

declare(strict_types=1);

namespace Ray\Compiler;

class FakeAop implements FakeAopInterface
{
    public function returnSame($a)
    {
        return $a;
    }
}
