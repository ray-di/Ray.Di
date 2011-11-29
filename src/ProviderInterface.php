<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Interface for object provider. (lazy-loading)
 *
 * @package Ray.Di
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
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