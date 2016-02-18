<?php
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
    public $value;
}
