<?php

declare(strict_types=1);

namespace Ray\Compiler;

use function dirname;
use function is_dir;
use Ray\Compiler\Exception\FileNotWritable;

final class FilePutContents
{
    public function __invoke(string $filename, string $content) : void
    {
        $dir = dirname($filename);
        ! is_dir($dir) && mkdir($dir, 0777, true);
        $tmpFile = tempnam(dirname($filename), 'swap');
        if (is_string($tmpFile) && file_put_contents($tmpFile, $content) && @rename($tmpFile, $filename)) {
            return;
        }
        @unlink((string) $tmpFile);

        throw new FileNotWritable($filename);
    }
}
