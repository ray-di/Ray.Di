<?php

declare(strict_types=1);

namespace Ray\Di\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

/**
 * Script directory
 *
 * This qualifier should not use in an application code.
 *
 * @Annotation
 * @Qualifier
 */
#[Attribute, Qualifier]
final class ScriptDir
{
}
