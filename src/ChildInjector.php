<?php
namespace Ray\Di;

/**
 * An injector that is capable of depending on an external injector for calculating dependencies.
 *
 * @package Ray\Di
 */
class ChildInjector extends Injector
{
    /** @var InstanceInterface */
    private $injector;

    /**
     * @param InstanceInterface $injector
     */
    public function setChildInjector(InstanceInterface $injector)
    {
        $this->injector = $injector;
    }

    /**
     * {@inheritDoc}
     */
    protected function safeGetInstance($class)
    {
        if ($this->injector instanceof InjectorInterface) {
            return $this->injector->getInstance( $class );
        }

        return parent::getInstance( $class );
    }

}
