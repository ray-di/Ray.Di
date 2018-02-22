<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

use Ray\Aop\MethodInvocation;
use Ray\Di\Exception\MethodInvocationNotAvailable;

class MethodInvocationProvider implements ProviderInterface
{
    /**
     * @var MethodInvocation|null
     */
    private $invocation;

    public function set(MethodInvocation $invocation)
    {
        $this->invocation = $invocation;
    }

    /**
     * @return MethodInvocation
     */
    public function get()
    {
        if (is_null($this->invocation)) {
            throw new MethodInvocationNotAvailable;
        }

        return $this->invocation;
    }
}
