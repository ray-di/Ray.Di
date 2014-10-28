<?php
/**
 * This file is part of the _package_ package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace Ray\Di;

class Interceptor
{
    private $interceptor;

    public function __construct($interceptor)
    {
        if (! class_exists($interceptor) && ! interface_exists($interceptor)) {
            throw new \InvalidArgumentException($interceptor);
        }
        $this->interceptor = $interceptor;
    }

    public function inject(Container $container)
    {
        return $container->getInstance($this->interceptor, Name::ANY);
    }

    public function __toString()
    {
        return $this->interceptor;
    }
}
