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
 * Matcher
 *
 * @package Aura.Di
 *
 */
class Matcher
{

    public function __construct($callable = null)
    {
        $this->callable = $callable;
    }

    public function any()
    {
        $func = function(){ return true;};
        return $func;
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
