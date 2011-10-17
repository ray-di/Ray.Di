<?php

namespace Ray\Di\Definition;

use Ray\Di\Mock\DbInterface,
    Ray\Di\Mock\UserInterface;

/**
 * Lifecycle test
 *
 */
class LifeCycle
{
    /**
     * After constrct.
     *
     * @PostConstruct
     */
    public function onInit()
    {
        $this->msg = '@PostConstruct';
    }

    /**
     * When container unset.
     *
     * @PreDestoroy
     */
    public function onEnd()
    {
        $GLOBALS['pre_destoroy'] = '@PreDestoroy';
    }
}