<?php

declare(strict_types=1);

namespace Ray\Di;

interface SetContextInterface
{
    /**
     * Set provider context
     *
     * @param string $context
     */
    public function setContext($context);
}
