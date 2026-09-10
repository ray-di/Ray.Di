<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * An unbound concrete class used as a constructor dependency in
 * FakeJitDepConsumer. It is never just-in-time bound (only top-level
 * getInstance() performs JIT), so it always surfaces as Unbound.
 */
class FakeJitDepConcrete
{
}
