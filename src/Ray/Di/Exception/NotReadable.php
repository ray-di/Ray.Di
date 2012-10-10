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
 * Indicates that there was a runtime failure while providing an instance.
 *
 * @package    Ray.Di
 * @subpackage Exception
 */
class NotReadable extends LogicException implements Exception
{
}
