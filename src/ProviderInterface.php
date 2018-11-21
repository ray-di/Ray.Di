<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * Interface for object provider. (lazy-loading)
 */
interface ProviderInterface
{
    /**
     * Get object
     */
    public function get();
}
