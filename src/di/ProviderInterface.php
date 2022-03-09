<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * @template T of mixed
 */
interface ProviderInterface
{
    /**
     * @return T
     */
    public function get();
}
