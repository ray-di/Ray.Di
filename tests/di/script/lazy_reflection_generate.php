<?php

declare(strict_types=1);

namespace Ray\Di;

use ReflectionParameter;
use RuntimeException;

use function dirname;
use function file_put_contents;
use function serialize;

require dirname(__DIR__, 3) . '/vendor/autoload.php';

[, $target, $outFile] = $argv;

$parameter = new ReflectionParameter([FakeLazyReflectionTarget::class, '__construct'], 'value');

$subject = match ($target) {
    'argument' => new Argument($parameter, Name::ANY),
    'injectionpoint' => new InjectionPoint($parameter),
    default => throw new RuntimeException('unknown target: ' . $target),
};

file_put_contents($outFile, serialize($subject));
