<?php

declare(strict_types=1);

namespace Ray\Di\Exception;

use RuntimeException;

/**
 * Thrown when the per-user class directory under the system temp directory
 * is a symlink, is owned by another user, or is writable by group or others.
 *
 * Message format: {directory path}
 *
 * Generated classes are loaded from that directory, so whoever can write to
 * it runs code as the application. Remove the directory, or pass a private
 * `tmpDir` to the Injector.
 */
final class DirectoryNotPrivate extends RuntimeException implements ExceptionInterface
{
}
