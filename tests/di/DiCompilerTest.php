<?php

declare(strict_types=1);

namespace Ray\Di;

use PHPUnit\Framework\TestCase;
use Ray\Compiler\DiCompiler;

class DiCompilerTest extends TestCase
{
    public function testUntargetInject() : void
    {
        /* @var $fake FakeUntarget */
        $module = new FakeUntargetToIntanceModule;
        $compiler = new DiCompiler($module, $_ENV['TMP_DIR']);
        $compiler->compile();
        $fake = $compiler->getInstance(FakeUntarget::class);
        $this->assertSame(1, $fake->child->val);
    }
}
