<?php

namespace Ray\Di\Modules;

use Ray\Di\ProviderInterface;

class StringProvider implements ProviderInterface
{
    /**
     * @return string 
     */
    public function get()
    {
        return 'provided string';
    }
}
