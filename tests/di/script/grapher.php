<?php

declare(strict_types=1);

namespace Ray\Di;

use function dirname;
use function file_put_contents;
use function serialize;

$appRoot = dirname(__DIR__, 3);
require $appRoot . '/vendor/autoload.php';

$grapher = new Grapher(new FakeAopGrapherModule(), $appRoot . '/tests/tmp');
file_put_contents(__FILE__ . '.txt', serialize($grapher));
