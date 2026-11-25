<?php

declare(strict_types=1);

namespace Ray\Di\Exception;

use RuntimeException;

final class EnvNotFound extends RuntimeException implements ExceptionInterface
{
}
