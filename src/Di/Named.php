<?php

declare(strict_types=1);

namespace Ray\Di\Di;

/**
 * Annotates named things
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Named
{
    /**
     * @var string
     */
    public $value = '';
}
