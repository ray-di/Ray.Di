<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Exception;

use LogicException;

/**
 * Invalid binding.
 *
 * @package Ray.Di
 */
class Binding extends LogicException implements Exception
{
}
