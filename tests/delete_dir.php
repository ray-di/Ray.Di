<?php

declare(strict_types=1);

namespace Ray\Compiler;

function delete_dir(string $path) : void
{
    foreach ((array) \glob(\rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $f) {
        $file = (string) $f;
        \is_dir($file) ? delete_dir($file) : \unlink($file);
        @\rmdir($file);
    }
}
