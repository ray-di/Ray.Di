<?php

declare(strict_types=1);

namespace Ray\Compiler\Exception;

use Ray\Di\Exception\Unbound as RayDiUnbound;

class NotCompiled extends RayDiUnbound implements ExceptionInterface
{
}
