<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * Alias of ProviderInterface
 *
 * @template T of mixed
 */
interface Provider extends ProviderInterface
{
    /**
     * @return T
     */
    public function get();
}
