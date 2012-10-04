<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind;

/**
 * Defines the interface for dependency injector logger.
 *
 * @package Ray.Di
 */
interface LoggerInterface
{
    /**
     * Injection logger
     *
     * @param string $class
     * @param array  $params
     * @param array  $setter
     * @param object $object
     * @param Bind   $bind
     *
     * @return void
     */
    public function log($class, array $params, array $setter, $object, Bind $bind);
}
