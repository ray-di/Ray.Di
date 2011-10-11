<?php

namespace Aura\Di\Definition;

use Aura\Di\Mock\DbInterface,
    Aura\Di\Mock\UserInterface;

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