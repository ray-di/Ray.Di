<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Doctrine\Common\Annotations\Annotation\Enum;
use Ray\Di\Di\InjectInterface;
use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier
 */
final class FakeLoggerInject implements InjectInterface
{
    /** @Enum({"MEMORY", "FILE", "DB"}) */
    public $type;

    public function isOptional()
    {
        return true;
    }
}
