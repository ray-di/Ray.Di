<?php

declare(strict_types=1);

namespace Ray\Di;

/**
 * Interface for context of object provider
 */
interface SetContextInterface
{
    /**
     * Set provider context
     */
    public function setContext($context);
}
