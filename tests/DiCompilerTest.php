<?php

namespace Ray\Di;

class DiCompilerTest extends \PHPUnit_Framework_TestCase
{
    private $files = [
        'Ray_Di_FakeCarInterface-*.php',
        'Ray_Di_FakeEngineInterface-*.php',
        'Ray_Di_FakeHandleInterface-*.php',
        'Ray_Di_FakeHardtopInterface-*.php',
        'Ray_Di_FakeMirrorInterface-right.php',
        'Ray_Di_FakeMirrorInterface-left.php',
        'Ray_Di_FakeTyreInterface-*.php',
    ];

    public function testCompile()
    {
        $compiler = new DiCompiler(new FakeCarModule, $_ENV['TMP_DIR']);
        $compiler->compile();
        foreach ($this->files as $file) {
            $this->assertTrue(file_exists($_ENV['TMP_DIR'] . '/'. $file));
        }
        $script = new ScriptInjector($_ENV['TMP_DIR']);
        $car = $script->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    public function testsGetInstance()
    {
        $compiler = new DiCompiler(new FakeCarModule, $_ENV['TMP_DIR']);
        $car = $compiler->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(FakeCar::class, $car);
    }
}
