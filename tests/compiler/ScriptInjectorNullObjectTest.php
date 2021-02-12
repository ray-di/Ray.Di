<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PHPUnit\Framework\TestCase;
use Ray\Aop\WeavedInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Exception\Unbound;
use Ray\Di\InjectorInterface;
use Ray\Di\NullModule;

use function assert;
use function count;
use function glob;
use function passthru;
use function serialize;
use function spl_object_hash;
use function sprintf;
use function unserialize;

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
