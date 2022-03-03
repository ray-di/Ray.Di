<?php

declare(strict_types=1);

namespace Ray\Di\MultiBinding;

use PHPUnit\Framework\TestCase;
use Ray\Di\FakeEngine;
use Ray\Di\FakeEngine2;
use Ray\Di\FakeEngineInterface;
use Ray\Di\MultiBinder;
use Ray\Di\NullModule;

/**
 * @requires PHP 8.0
 */
class MultiBinderTest extends TestCase
{
    public function testAdd(): void
    {
        $module = new NullModule();
        $binder = MultiBinder::newInstance($module, FakeEngineInterface::class);
        $binder->addBinding('one')->to(FakeEngine::class);
        $binder->addBinding('two')->to(FakeEngine2::class);
        /** @var MultiBindings $multiBindings */
        $multiBindings = $module->getContainer()->getInstance(MultiBindings::class);
        $this->assertArrayHasKey('one', (array) $multiBindings[FakeEngineInterface::class]);
        $this->assertArrayHasKey('two', (array) $multiBindings[FakeEngineInterface::class]);
    }

    public function testSet(): void
    {
        $module = new NullModule();
        $binder = MultiBinder::newInstance($module, FakeEngineInterface::class);
        $binder->addBinding('one')->to(FakeEngine::class);
        $binder->addBinding('two')->to(FakeEngine2::class);
        $binder->setBinding('one')->to(FakeEngine::class);
        /** @var MultiBindings $multiBindings */
        $multiBindings = $module->getContainer()->getInstance(MultiBindings::class);
        $this->assertArrayHasKey('one', (array) $multiBindings[FakeEngineInterface::class]);
        $this->assertArrayNotHasKey('two', (array) $multiBindings[FakeEngineInterface::class]);
    }
}
