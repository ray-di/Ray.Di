<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier
 */
class FakeLeft
{
    public $value;
}
