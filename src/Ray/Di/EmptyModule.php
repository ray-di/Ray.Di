<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Empty Module.
 */
class EmptyModule extends AbstractModule
{
    public function __construct()
    {
        $this->bindings = new \ArrayObject;
        $this->container = new \ArrayObject;
        $this->configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
    }
}
