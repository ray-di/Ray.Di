<?php
/**
 * This file is part of the Ray package.
 *
 * @package    Ray.Di
 * @subpackage Exception
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Exception;

use LogicException;

/**
 * Not instantiable.
 *
 * @package    Ray.Di
 * @subpackage Exception
 */
class NotInstantiable extends LogicException implements Exception
{
}
