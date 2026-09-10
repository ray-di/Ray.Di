<?php

declare(strict_types=1);

namespace Ray\Di;

use RuntimeException;

use function assert;
use function class_exists;
use function dirname;
use function file_get_contents;
use function unserialize;

require dirname(__DIR__, 3) . '/vendor/autoload.php';

[, $target, $blobFile] = $argv;

$targetClass = FakeLazyReflectionTarget::class;

if (class_exists($targetClass, false)) {
    echo 'FAIL:already-loaded-before-unserialize';
    exit(1);
}

$subject = unserialize((string) file_get_contents($blobFile));

if (class_exists($targetClass, false)) {
    echo 'FAIL:loaded-by-unserialize';
    exit(1);
}

if ($target === 'argument') {
    assert($subject instanceof Argument);
    $subject->get();
} elseif ($target === 'injectionpoint') {
    assert($subject instanceof InjectionPoint);
    $subject->getParameter();
} else {
    throw new RuntimeException('unknown target: ' . $target);
}

if (! class_exists($targetClass, false)) {
    echo 'FAIL:not-loaded-after-get';
    exit(1);
}

echo 'PASS';
