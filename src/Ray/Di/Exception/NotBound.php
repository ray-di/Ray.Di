<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Exception;

use Ray\Di\AbstractModule;

class NotBound extends Binding implements ExceptionInterface
{
    /**
     * @var AbstractModule
     */
    public $module;

    /**
     * @param AbstractModule $module
     *
     * @return NotBound
     */
    public function setModule(AbstractModule $module)
    {
        $this->module = $module;

        return $this;
    }
}
