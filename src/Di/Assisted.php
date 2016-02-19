<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di\Di;

use Ray\Aop\Annotation\AbstractAssisted;

/**
 * Annotates your class methods into which the Injector should pass the values on method invocation
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Assisted extends AbstractAssisted
{
}
