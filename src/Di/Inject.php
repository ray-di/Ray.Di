<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Di;

/**
 * Inject
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Inject
{
    /**
     * Optional ?
     *
     * @var bool
     */
    public $optional = false;
}
