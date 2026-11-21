<?php

declare(strict_types=1);

namespace Ray\Di;

use Ray\Di\Exception\DirectoryNotPrivate;
use Ray\Di\Exception\DirectoryNotWritable;

use function clearstatcache;
use function fileowner;
use function is_writable;
use function lstat;
use function mkdir;
use function sprintf;
use function tempnam;
use function unlink;

use const DIRECTORY_SEPARATOR;

/**
 * Per-user directory under a shared temp directory, whose sticky bit keeps others from replacing it
 *
 * @internal
 */
final class PrivateTmpDir
{
    private const WRITABLE_BY_GROUP_OR_OTHERS = 0022;
    private const FILE_TYPE = 0170000;
    private const DIRECTORY = 0040000;

    public function __invoke(string $baseDir): string
    {
        // @codeCoverageIgnoreStart
        if (DIRECTORY_SEPARATOR === '\\') {
            return $baseDir; // no POSIX owner/mode
        }

        // @codeCoverageIgnoreEnd

        $uid = $this->uid($baseDir);
        $dir = sprintf('%s/ray-di-%d', $baseDir, $uid);
        @mkdir($dir, 0700);
        clearstatcache(true, $dir);
        // lstat does not follow a symlink
        $stat = @lstat($dir);
        $isPrivate = $stat !== false
            && ($stat['mode'] & self::FILE_TYPE) === self::DIRECTORY
            && $stat['uid'] === $uid
            && ($stat['mode'] & self::WRITABLE_BY_GROUP_OR_OTHERS) === 0;
        if (! $isPrivate) {
            throw new DirectoryNotPrivate($dir);
        }

        return $dir;
    }

    /**
     * uid of the current user without ext-posix
     */
    private function uid(string $baseDir): int
    {
        $probe = is_writable($baseDir) ? tempnam($baseDir, 'di') : false;
        $uid = $probe === false ? false : fileowner($probe);
        // @codeCoverageIgnoreStart
        if ($probe === false || $uid === false) {
            throw new DirectoryNotWritable($baseDir);
        }

        // @codeCoverageIgnoreEnd

        @unlink($probe);

        return $uid;
    }
}
