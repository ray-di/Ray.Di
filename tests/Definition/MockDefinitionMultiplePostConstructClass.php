<?php

namespace Ray\Di\Definition;

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
