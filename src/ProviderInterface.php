<?php

/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

/**
 * Interface for object provider. (lazy-loading)
 */
interface ProviderInterface
{
    /**
     * Get object
     *
     * @return mixed
     */
    public function get();
}
