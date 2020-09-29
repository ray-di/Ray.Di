<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * @Annotation
 * @Target("CLASS")
 */
class FakeAnnoClass
{
    public static $order = [];
}
