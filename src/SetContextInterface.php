<?php

declare(strict_types=1);

namespace Ray\Di;

interface SetContextInterface
{
    /**
     * Set provider context
     *
     * @psalm-suppress MissingReturnType
     * @psalm-suppress MissingParamType
     */
    public function setContext($context);
}
