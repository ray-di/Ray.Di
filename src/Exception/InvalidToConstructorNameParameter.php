<?php

declare(strict_types=1);

namespace Ray\Di\Exception;

use InvalidArgumentException;

/**
 * @see https://github.com/ray-di/Ray.Di#constructor-bindings
 */
class InvalidToConstructorNameParameter extends InvalidArgumentException implements ExceptionInterface
{
}
