<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Aop\MethodInvocation;
use Ray\Di\Exception\MethodInvocationNotAvailable;

/**
 * @implements ProviderInterface<MethodInvocation>
 */
final class MethodInvocationProvider implements ProviderInterface
{
    /** @var ?MethodInvocation */
    private $invocation;

    public function set(MethodInvocation $invocation): void
    {
        $this->invocation = $invocation;
    }

    public function get(): MethodInvocation
    {
        if ($this->invocation === null) {
            throw new MethodInvocationNotAvailable();
        }

        return $this->invocation;
    }
}
