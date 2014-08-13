<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind;

/**
 * Interface for dependency injector logger.
 */
interface LoggerInterface
{
    /**
     * log prototype instance
     *
     * @param BoundDefinition $definition
     * @param array           $params
     * @param array           $setter
     * @param object          $object
     * @param Bind            $bind
     * @param bool            $isSingleton
     *
     * @return void
     */
    public function log(BoundDefinition $definition, array $params, array $setter, $object, Bind $bind);
}
