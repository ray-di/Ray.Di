<?php

namespace Ray\Di;

use Ray\Di\Exception\Unbound;

class ScriptInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ScriptInjector
     */
    private $injector;

    public function setUp()
    {
        $compiler = new DiCompiler(new FakeCarModule, $_ENV['TMP_DIR']);
        $compiler->compile();
        $this->injector = new ScriptInjector($_ENV['TMP_DIR']);
        parent::setUp();
    }

    public function testGetInstance()
    {
        $car = $this->injector->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    public function testCompileException()
    {
        $this->setExpectedException(Unbound::class);
        $script = new ScriptInjector($_ENV['TMP_DIR']);
        $script->getInstance('invalid-class');
    }

    public function testSingleton()
    {
        (new DiCompiler(new FakeToBindSingletonModule, $_ENV['TMP_DIR']))->compile();
        $instance1 = $this->injector->getInstance(FakeRobotInterface::class);
        $instance2 = $this->injector->getInstance(FakeRobotInterface::class);
        $this->assertSame(spl_object_hash($instance1), spl_object_hash($instance2));
    }
}
