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
        $binder->add(FakeEngine::class, 'one');
        $binder->add(FakeEngine2::class, 'two');
        /** @var LazyCollection $lazyCollection */
        $lazyCollection = $module->getContainer()->getInstance(LazyCollection::class);
        $this->assertArrayHasKey('one', $lazyCollection[FakeEngineInterface::class]);
        $this->assertArrayHasKey('two', $lazyCollection[FakeEngineInterface::class]);
    }

    public function testSet(): void
    {
        $module = new NullModule();
        $binder = MultiBinder::newInstance($module, FakeEngineInterface::class);
        $binder->add(FakeEngine::class, 'one');
        $binder->add(FakeEngine2::class, 'two');
        $binder->set(FakeEngine::class, 'one');
        /** @var LazyCollection $lazyCollection */
        $lazyCollection = $module->getContainer()->getInstance(LazyCollection::class);
        $this->assertArrayHasKey('one', $lazyCollection[FakeEngineInterface::class]);
        $this->assertArrayNotHasKey('two', $lazyCollection[FakeEngineInterface::class]);
    }
}
