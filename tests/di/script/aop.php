<?php

declare(strict_types=1);

namespace Ray\Di;

use function dirname;
use function file_put_contents;
use function serialize;

$appRoot = dirname(__DIR__, 3);
require $appRoot . '/vendor/autoload.php';

$injector = new Injector(new FakeAopModule(), $appRoot . '/tests/tmp');
file_put_contents(__FILE__ . '.cache.txt', serialize($injector));
