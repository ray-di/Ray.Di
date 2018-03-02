<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Di package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Di;

$appRoot = dirname(dirname(__DIR__));
require $appRoot . '/vendor/autoload.php';

$injector = new Injector(new FakeAopModule, $appRoot . '/tests/tmp');
file_put_contents(__FILE__ . '.cache', serialize($injector));
