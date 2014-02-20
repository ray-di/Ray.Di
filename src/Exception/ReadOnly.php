<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Exception;

use RuntimeException;

class ReadOnly extends RuntimeException implements ExceptionInterface
{
}
