<?php

namespace Ray\Di\Definition;

use Ray\Di\Di\Scope;
use Ray\Di\Di\PostConstruct;

/**
 * @Scope("prototype")
 */
class MockDefinitionMultiplePostConstructClass
{
    /**
     * Init
     *
     * @PostConstruct
     */
    public function onInit()
    {
        $this->inited = true;
    }

    /**
     * Init
     *
     * @PostConstruct
     */
    public function onInit2()
    {
        $this->inited = true;
    }
}
