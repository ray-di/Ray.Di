<?php
/**
 * This file is part of the Ray package.
 *
 * @package    Ray.Di
 * @subpackage Exception
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Exception;

use Ray\Di\AbstractModule;

/**
 * Indicates that there was a runtime failure while providing an instance.
 *
 * @package    Ray.Di
 * @subpackage Exception
 */
class NotBound extends Binding implements Exception
{
    /**
     * @var Ray\Di\Module
     */
    public $module;

    /**
     * @param \Ray\Di\AbstractModule $module
     *
     * @return NotBound
     */
    public function setModule(AbstractModule $module)
    {
        $this->module = $module;
        return $this;
    }
}
