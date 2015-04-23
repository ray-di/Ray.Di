<?php

namespace Ray\Di;

use Ray\Di\Exception\NotCompiled;

class ScriptInjectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $compiler = new DiCompiler(new FakeCarModule, $_ENV['TMP_DIR']);
        $compiler->compile();
        parent::setUp();
    }

    public function testGetInstance()
    {
        $script = new ScriptInjector($_ENV['TMP_DIR']);
        $car = $script->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    public function testCompileException()
    {
        $this->setExpectedException(NotCompiled::class);
        $script = new ScriptInjector($_ENV['TMP_DIR']);
        $script->getInstance('invalid-class');
    }
}
