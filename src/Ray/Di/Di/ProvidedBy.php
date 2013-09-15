<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Di;

/**
 * ProvidedBy
 *
 * @Annotation
 * @Target("CLASS")
 */
final class ProvidedBy implements Annotation
{
    /**
     * Provided class name
     *
     * @var string
     */
    public $value;
}
