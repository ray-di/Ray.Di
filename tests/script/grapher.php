<?php

declare(strict_types=1);

namespace Ray\Di;

$appRoot = dirname(__DIR__, 2);
require $appRoot . '/vendor/autoload.php';

$grapher = new Grapher(new FakeAopGrapherModule, $appRoot . '/tests/tmp');
file_put_contents(__FILE__ . '.txt', serialize($grapher));
