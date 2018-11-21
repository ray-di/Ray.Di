<?php

declare(strict_types=1);

namespace Ray\Di;

final class Scope
{
    /**
     * Singleton scope
     *
     * @var string
     */
    const SINGLETON = 'Singleton';

    /**
     * Prototype scope
     *
     * @var string
     */
    const PROTOTYPE = 'Prototype';
}
