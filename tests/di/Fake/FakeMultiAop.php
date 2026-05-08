<?php

declare(strict_types=1);

namespace Ray\Di;

class FakeMultiAop implements FakeMultiAopInterface
{
    public function methodA()
    {
        return 'a';
    }

    public function methodB()
    {
        return 'b';
    }
}
