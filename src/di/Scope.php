<?php

declare(strict_types=1);

namespace Ray\Di;

final class Scope
{
    /**
     * Singleton scope
     */
    public const SINGLETON = 'Singleton';

    /**
     * Prototype scope
     */
    public const PROTOTYPE = 'Prototype';
}
