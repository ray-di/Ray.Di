<?php

declare(strict_types=1);

namespace Ray\Compiler;

use ReflectionParameter;

use function serialize;

final class IpQualifier
{
    /** @var ReflectionParameter */
    public $param;

    /** @var mixed */
    public $qualifier;

    public function __construct(ReflectionParameter $param, object $qualifier)
    {
        $this->param = $param;
        $this->qualifier = $qualifier;
    }

    public function __toString(): string
    {
        return serialize($this->qualifier);
    }
}
