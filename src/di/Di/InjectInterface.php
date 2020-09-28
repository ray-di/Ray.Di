<?php

declare(strict_types=1);

namespace Ray\Di\Di;

interface InjectInterface
{
    /**
     * Whether or not to use optional injection
     *
     * @return bool
     */
    public function isOptional();
}
