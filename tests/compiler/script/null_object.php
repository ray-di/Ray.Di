<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

use Ray\Compiler\FakeNullObjectModule;
use Ray\Compiler\FakeTyreInterface;
use Ray\Compiler\ScriptInjector;

$injector = new ScriptInjector(
    dirname(__DIR__) . '/tmp',
    static function () {
        return new FakeNullObjectModule();
    }
);
$instance = $injector->getInstance(FakeTyreInterface::class);
