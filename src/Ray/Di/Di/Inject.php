<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Di;

/**
 * Inject
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @package    Ray.Di
 * @subpackage Annotation
 */
final class Inject implements Annotation
{
    public $optional = false;
}
