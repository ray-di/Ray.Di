<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Interface for ModuleString.
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
