<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use PHPUnit\Framework\TestCase;
use Ray\Di\FakeEngine;
use Ray\Di\FakeEngine2;
use Ray\Di\FakeEngineInterface;
use Ray\Di\NullModule;

class MultiBinderTest extends TestCase
{
    public function testAdd(): void
    {
        $module = new NullModule();
        $binder = MultiBinder::newInstance($module, FakeEngineInterface::class);
        $binder->add('one', FakeEngine::class);
        $binder->add('two', FakeEngine2::class);
        $lazyCollection = $module->getContainer()->getInstance(LazyCollection::class);
        $this->assertArrayHasKey('one', $lazyCollection[FakeEngineInterface::class]);
        $this->assertArrayHasKey('two', $lazyCollection[FakeEngineInterface::class]);
    }
}
