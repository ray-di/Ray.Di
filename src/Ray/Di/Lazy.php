<?php
/**
 * Ray
 *
 * This file is taken from Aura Project and modified. (namespace only)
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 *
 * Wraps a callable specifically for the purpose of lazy-loading an object.
 *
 * @package Aura.Di
 *
 */
class Lazy
{
    /**
     *
     * A callable to create an object instance.
     *
     * @var callable
     *
     */
    protected $callable;

    /**
     *
     * Constructor.
     *
     * @param \Closure $closure A closure that creates an object instance.
     *
     * @return void
     *
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     *
     * Invokes the closure to create the instance.
     *
     * @return object The object created by the closure.
     *
     */
    public function __invoke()
    {
        $callable = $this->callable;
        return $callable();
    }
}
