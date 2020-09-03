<?php
namespace Ray\Di;

class FakeAop implements FakeAopInterface
{
    public function returnSame($a)
    {
        return $a;
    }
}
