<?php

namespace Ray\Di\Definition;

use Ray\Di\Di\PreDestroy;
use Ray\Di\Di\PostConstruct;

class LifeCycle
{
    public $msg;

    /**
     * @PostConstruct
     */
    public function onInit()
    {
        $this->msg = '@PostConstruct';
    }

    /**
     * @PreDestroy
     */
    public function onEnd()
    {
        $GLOBALS['pre_destroy'] = '@PreDestroy';
    }
}
