<?php

declare(strict_types=1);

$_ENV['TMP_DIR'] = __DIR__ . '/tmp';

// cleanup tmp directory
(function (): void {
    $unlink = function (string $path) use (&$unlink): callable {
        assert(is_callable($unlink));
        foreach (array_filter((array) glob($path . '/*')) as $file) {
            is_dir($file) ? $unlink($file) : unlink($file);
            @rmdir($file);
        }
        return $unlink;
    };
    $unlink(__DIR__ . '/tmp')(__DIR__ . '/compiler/tmp');
})();
