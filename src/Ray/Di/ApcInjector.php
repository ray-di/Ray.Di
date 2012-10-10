<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Dependency Injector using APC
 *
 * @package Ray.Di
 */
class ApcInjector extends Injector
{
    /**
     * Apc prefix key
     *
     * @var string
     */
    private $prefix;

    /**
     * (non-PHPdoc)
     * @see \
     *
     * @param string $class
     * @param array  $params
     *
     * @return object
     */
    public function getInstance($class, array $params = null)
    {
        $this->prefix = md5($this->module);
        $object = apc_fetch($this->prefix . $class, $success);
        $object = $object ? : parent::getInstance($class, $params);
        if ($success !== true) {
            apc_store($this->prefix . $class, $object);
        }

        return $object;
    }
}
