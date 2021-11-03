<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Compiler\DiCompiler;

use function assert;
use function is_object;
use function property_exists;

class DiCompilerTest extends TestCase
{
    public function testUntargetInject(): void
    {
        $module = new FakeUntargetToIntanceModule();
        $compiler = new DiCompiler($module, __DIR__ . '/tmp');
        $compiler->compile();
        $fake = $compiler->getInstance(FakeUntarget::class);
        assert(is_object($fake));
        assert(property_exists($fake, 'child'));
        assert(property_exists($fake->child, 'val'));
        $this->assertSame(1, $fake->child->val);
    }
}
