<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PHPUnit\Framework\TestCase;

use function passthru;
use function sprintf;

class ScriptInjectorNullObjectTest extends TestCase
{
    public function testNullObjectCompile(): ScriptInjector
    {
        passthru(sprintf('php %s/script/null_object.php', __DIR__));

        $injector = new ScriptInjector(
            __DIR__ . '/tmp',
            static function () {
                return new FakeNullObjectModule();
            }
        );
        $instance = $injector->getInstance(FakeTyreInterface::class);
        $this->assertInstanceOf(FakeTyreInterface::class, $instance);

        return $injector;
    }
}
