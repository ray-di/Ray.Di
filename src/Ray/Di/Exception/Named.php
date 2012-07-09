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
 * Invalid @Named annotation.
 *
 * @package Ray.Di
 */
class Named extends Binding implements Exception
{
}
