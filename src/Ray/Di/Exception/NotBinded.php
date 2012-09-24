<?php
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Exception;

/**
 * Indicates that there was a runtime failure while providing an instance.
 *
 * @package Ray.Di
 */
class NotBinded extends Binding implements Exception
{
}
