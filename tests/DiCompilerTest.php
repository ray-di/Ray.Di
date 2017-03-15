<?php
namespace Ray\Di;

use Ray\Compiler\DiCompiler;

class DiCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testUntargetInject()
    {
        /* @var $fake FakeUntarget */
        $module = new FakeUntargetModule;
        $compiler = new DiCompiler($module, $_ENV['TMP_DIR']);
        $compiler->compile();
        $fake = $compiler->getInstance(FakeUntarget::class);
        $this->assertSame(1, $fake->child->val);
    }
}
