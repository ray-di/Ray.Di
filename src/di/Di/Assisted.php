<?php

declare(strict_types=1);

namespace Ray\Di\Di;

use Attribute;
use Ray\Aop\Annotation\AbstractAssisted;

#[Attribute]
/**
 * Annotates your class methods into which the Injector should pass the values on method invocation
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Assisted extends AbstractAssisted
{
}
