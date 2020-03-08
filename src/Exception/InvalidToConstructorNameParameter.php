<?php

declare(strict_types=1);

namespace Ray\Di\Exception;

/**
 * @see https://github.com/ray-di/Ray.Di#constructor-bindings
 */
class InvalidToConstructorNameParameter extends \InvalidArgumentException implements ExceptionInterface
{
}
