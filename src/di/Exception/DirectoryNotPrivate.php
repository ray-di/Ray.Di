<?php

declare(strict_types=1);

namespace Ray\Di\Exception;

use RuntimeException;

/**
 * Thrown when the per-user class directory is a symlink, owned by another user, or writable by group or others.
 *
 * Message format: {directory path}
 */
final class DirectoryNotPrivate extends RuntimeException implements ExceptionInterface
{
}
