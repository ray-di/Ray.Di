<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

/**
 * Empty Module
 *
 * @package Ray.Di
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
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