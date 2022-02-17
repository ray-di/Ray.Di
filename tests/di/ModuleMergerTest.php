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
                }
            },
            new class extends AbstractModule
            {
                protected function configure()
                {
                    $this->bind()->annotatedWith('var')->toInstance('b');
                }
            },
        ];
        $module = (new ModuleMerger())($modules);
        $var = (new Injector($module))->getInstance('', 'var');
        $this->assertSame('b', $var);
    }
}
