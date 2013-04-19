<?php

namespace Ray\Di\Modules;

use Ray\Di\ProviderInterface;
use Ray\Di\Mock\Reader;

class ReaderProvider implements ProviderInterface
{
    /**
     * @return Reader
     */
    public function get()
    {
        $instance = new Reader();

        return $instance;
    }
}
