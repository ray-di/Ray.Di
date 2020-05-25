<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\MethodInvocation;
use Ray\Di\Exception\MethodInvocationNotAvailable;

class MethodInvocationProvider implements ProviderInterface
{
    /**
     * @var ?MethodInvocation
     */
    private $invocation;

    public function set(MethodInvocation $invocation) : void
    {
        $this->invocation = $invocation;
    }

    /**
     * @return MethodInvocation
     */
    public function get()
    {
        if ($this->invocation === null) {
            throw new MethodInvocationNotAvailable;
        }

        return $this->invocation;
    }
}
