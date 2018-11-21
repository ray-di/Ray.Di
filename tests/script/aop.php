<?php

declare(strict_types=1);

namespace Ray\Di;

$appRoot = dirname(__DIR__, 2);
require $appRoot . '/vendor/autoload.php';

$injector = new Injector(new FakeAopModule, $appRoot . '/tests/tmp');
file_put_contents(__FILE__ . '.cache.txt', serialize($injector));
