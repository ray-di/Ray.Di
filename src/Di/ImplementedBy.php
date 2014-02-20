<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Di;

/**
 * Default implementation
 *
 * @Annotation
 * @Target("CLASS")
 *
 * @subpackage Annotation
 */
final class ImplementedBy implements Annotation
{
    /**
     * Default class
     *
     * @var string
     */
    public $value;
}
