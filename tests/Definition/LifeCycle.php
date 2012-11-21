<?php

namespace Ray\Di\Definition;

use Ray\Di\Di\PreDestroy;
use Ray\Di\Di\PostConstruct;

/**
 * Lifecycle test
 *
 */
class LifeCycle
{
    /**
     * @PostConstruct
     */
    public function onInit()
    {
        $this->msg = '@PostConstruct';
    }

    /**
     * When container unset.
     *
     * @PreDestroy
     */
    public function onEnd()
    {
        $GLOBALS['pre_destroy'] = '@PreDestroy';
    }
}
