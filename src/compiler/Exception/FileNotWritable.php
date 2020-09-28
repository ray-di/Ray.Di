<?php

declare(strict_types=1);

namespace Ray\Compiler\Exception;

use RuntimeException;

class FileNotWritable extends RuntimeException implements ExceptionInterface
{
}
