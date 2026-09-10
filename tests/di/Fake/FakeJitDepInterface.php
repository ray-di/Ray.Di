<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * A minimal interface for the JIT deprecation contract test: its
 * implementation depends on an unbound concrete class, so resolving the
 * interface top-level surfaces the interior dependency as Unbound without
 * emitting a just-in-time deprecation notice.
 */
interface FakeJitDepInterface
{
}
