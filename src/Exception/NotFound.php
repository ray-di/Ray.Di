<?php

declare(strict_types=1);

namespace Ray\Di\Exception;

use LogicException;

class NotFound extends LogicException implements ExceptionInterface
{
}
