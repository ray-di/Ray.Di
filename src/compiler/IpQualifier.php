<?php

declare(strict_types=1);

namespace Ray\Compiler;

final class IpQualifier
{
    /**
     * @var \ReflectionParameter
     */
    public $param;

    /**
     * @var mixed
     */
    public $qualifier;

    public function __construct(\ReflectionParameter $param, object $qualifier)
    {
        $this->param = $param;
        $this->qualifier = $qualifier;
    }

    public function __toString()
    {
        return \serialize($this->qualifier);
    }
}
