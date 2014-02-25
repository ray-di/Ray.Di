<?php

namespace Ray\Di\Definition;

use Ray\Di\Di\Scope;
use Ray\Di\Di\PostConstruct;

/**
 * @Scope("prototype")
 */
class MockDefinitionMultiplePostConstructClass
{
    public $inited;

    /**
     * @PostConstruct
     */
    public function onInit()
    {
        $this->inited = true;
    }

    /**
     * @PostConstruct
     */
    public function onInit2()
    {
        $this->inited = true;
    }
}
