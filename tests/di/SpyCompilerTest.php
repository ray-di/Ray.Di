<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Bind as AopBind;

class SpyCompilerTest extends TestCase
{
    public function testNoBindng(): void
    {
        $spy = new SpyCompiler();
        $class = $spy->compile(FakeHandle::class, new AopBind());
        $this->assertSame(FakeHandle::class, $class);
    }
}
