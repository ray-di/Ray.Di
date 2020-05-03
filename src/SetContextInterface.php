<?php

declare(strict_types=1);

namespace Ray\Di;

interface SetContextInterface
{
    /**
     * Set provider context
     *
     * @param string $context
     *
     * @return void
     */
    public function setContext($context);
}
