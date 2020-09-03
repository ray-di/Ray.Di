<?php

namespace Ray\Compiler;

class FakeAop implements FakeAopInterface
{
    public function returnSame($a)
    {
        return $a;
    }
}
