<?php

declare(strict_types=1);

namespace Ray\Di\Di;

interface InjectInterface
{
    /** @noinspection ReturnTypeCanBeDeclaredInspection for BC */

    /**
     * Whether or not to use optional injection
     *
     * @return bool
     */
    public function isOptional();
}
