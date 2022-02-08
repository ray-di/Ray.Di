<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Named;

class FakePropConstruct
{
    public function __construct(
        #[Named('abc')] public readonly string $abc)
    {
    }
}
