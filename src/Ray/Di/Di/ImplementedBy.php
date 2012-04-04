<?php
/**
 * Ray
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Di;

/**
 * ImplememtedBy
 *
 * @Annotation
 * @Target("CLASS")
 *
 * @package    Ray.Di
 * @subpackage Annotation
 */
final class ImplementedBy implements Annotation
{
    public $value;
}
