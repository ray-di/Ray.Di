<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * An implementation of FakeJitDepInterface that depends on an unbound
 * FakeJitDepConcrete.
 */
class FakeJitDepConsumer implements FakeJitDepInterface
{
    public function __construct(public FakeJitDepConcrete $dependency)
    {
    }
}
