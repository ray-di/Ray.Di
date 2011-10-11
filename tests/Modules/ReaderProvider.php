<?php

namespace Aura\Di\Modules;

use Aura\Di\ProviderInterface,
    Aura\Di\Mock\Reader;

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