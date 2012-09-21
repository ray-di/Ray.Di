<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Di;

/**
 * ProvidedBy
 *
 * @Annotation
 * @Target("CLASS")
 *
 * @package    Ray.Di
 * @subpackage Annotation
 */
final class ProvidedBy implements Annotation
{
    public $value;
}
