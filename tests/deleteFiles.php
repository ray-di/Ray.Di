<?php

declare(strict_types=1);

namespace Ray\Compiler;

use function array_filter;
use function glob;
use function is_dir;
use function rmdir;
use function unlink;

function deleteFiles(string $path): void
{
    foreach (array_filter((array) glob($path . '/*')) as $file) {
        is_dir($file) ? deleteFiles($file) : unlink($file);
        @rmdir($file);
    }
}
