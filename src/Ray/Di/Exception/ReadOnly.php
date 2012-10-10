<?php
/**
 * This file is part of the Ray package.
 *
 * @package    Ray.Di
 * @subpackage Exception
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Exception;

use RuntimeException;

/**
 * Read only.
 *
 * @package    Ray.Di
 * @subpackage Exception
 */
class ReadOnly extends RuntimeException implements Exception
{
}
