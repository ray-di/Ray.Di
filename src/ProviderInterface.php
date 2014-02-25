<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
     * @return \Ray\Aop\Compiler
     */
    public function get();
}
