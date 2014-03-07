<?php

namespace Ray\Di\Definition;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class MockScalar
{
    /**
     * @var mixed
     */
    public $injected;

    /**
     * @param mixed $injected
     *
     * @Inject
     * @Named("scalar_value")
     */
    public function setScalar($injected)
    {
        $this->injected = $injected;
    }
}
