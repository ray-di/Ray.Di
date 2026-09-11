<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * Used only by LazyReflectionAutoloadTest's out-of-process fixture scripts.
 * Deliberately unreferenced elsewhere so it stays unloaded until
 * get()/getParameter() reconstructs its ReflectionParameter.
 */
final class FakeLazyReflectionTarget
{
    public function __construct(string $value)
    {
    }
}
