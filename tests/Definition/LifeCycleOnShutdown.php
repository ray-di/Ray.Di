<?php

namespace Ray\Di\Definition;

use Ray\Di\Di\PreDestroy;
use Ray\Di\Di\PostConstruct;

class LifeCycleOnShutdown
{
    /**
     * @PreDestroy
     */
    public function onEnd()
    {
        $GLOBALS['PreDestroy_on_shutdown'] = '@PreDestroy';
    }
}
