<?php

namespace Ray\Compiler;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

class FakeHandleProvider implements ProviderInterface
{
    private $logo;

    /**
     * @Inject
     * @Named("logo")
     */
    public function __construct($logo = 'nardi')
    {
        $this->logo = $logo;
    }

    public function get()
    {
        $handle = new FakeHandle;
        $handle->logo = $this->logo;

        return $handle;
    }
}
