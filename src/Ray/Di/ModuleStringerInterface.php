<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * ModuleStringerInterface
 *
 * @package Ray\Di
 */
interface ModuleStringerInterface
{
    /**
     * @param AbstractModule $module
     *
     * @return string
     */
    public function toString(AbstractModule $module);
}
