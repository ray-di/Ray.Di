<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\AbstractModule;

/**
 * Empty Module
 *
 * @package Ray.Di
 */
class EmptyModule extends AbstractModule
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bindings = new \ArrayObject;
        $this->container = new \ArrayObject;
        $this->configure();
    }

    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
    }
}
