<?php

declare(strict_types=1);

namespace Ray\Di\Di;

use Attribute;

#[Attribute]
/**
 * Annotates named things
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Named
{
    /** @var string */
    public $value = '';
}
