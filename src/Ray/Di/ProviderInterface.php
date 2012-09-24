<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Interface for object provider. (lazy-loading)
 *
 * @package Ray.Di
 */
interface ProviderInterface
{
    /**
     * Get object
     *
     * @return object
     */
    public function get();
}
