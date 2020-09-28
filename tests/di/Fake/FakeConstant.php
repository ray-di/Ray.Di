<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target({"CLASS","METHOD"})
 * @Qualifier
 */
final class FakeConstant
{
    public $value;
}
