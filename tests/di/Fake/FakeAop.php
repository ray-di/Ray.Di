<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeAop implements FakeAopInterface
{
    public function returnSame($a)
    {
        return $a;
    }
}
