<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;

class ModuleMergerTest extends TestCase
{
    public function testInvoke(): void
    {
        $modules = [
            new class extends AbstractModule
            {
                protected function configure()
                {
                    $this->bind()->annotatedWith('var')->toInstance('a');
                    $this->bind()->annotatedWith('first')->toInstance('1');
                }
            },
            new class extends AbstractModule
            {
                protected function configure()
                {
                    $this->bind()->annotatedWith('var')->toInstance('b');
                    $this->bind()->annotatedWith('second')->toInstance('2');
                }
            },
        ];
        $injector = new Injector($modules);
        $this->assertSame('a', $injector->getInstance('', 'var'));
        $this->assertSame('2', $injector->getInstance('', 'second'));
        $this->assertSame('1', $injector->getInstance('', 'first'));
    }
}
