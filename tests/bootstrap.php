<?php

declare(strict_types=1);

$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
// cleanup tmp directory
$unlink = function ($path) use (&$unlink) {
    foreach ((array) glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $f) {
        $file = (string) $f;
        is_dir($file) ? $unlink($file) : unlink($file);
        @rmdir($file);
    }
};
$unlink($_ENV['TMP_DIR']);
