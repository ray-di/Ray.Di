<?php

namespace Ray\Di;

use Ray\Aop\WeavedInterface;
use Ray\Di\Exception\NotCompiled;

class DiCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testNotCompiled()
    {
        $this->setExpectedException(NotCompiled::class);
        $injector = new ScriptInjector($_ENV['TMP_DIR']);
        $car = $injector->getInstance(FakeCarInterface::class);

    }
    public function testCompile()
    {
        $compiler = new DiCompiler(new FakeCarModule, $_ENV['TMP_DIR']);
        $compiler->compile();
        $files = [
            'Ray_Di_FakeCarInterface-*.php',
            'Ray_Di_FakeEngineInterface-*.php',
            'Ray_Di_FakeHandleInterface-*.php',
            'Ray_Di_FakeHardtopInterface-*.php',
            'Ray_Di_FakeMirrorInterface-right.php',
            'Ray_Di_FakeMirrorInterface-left.php',
            'Ray_Di_FakeTyreInterface-*.php',
        ];
        foreach ($files as $file) {
            $this->assertTrue(file_exists($_ENV['TMP_DIR'] . '/'. $file));
        }
        $injector = new ScriptInjector($_ENV['TMP_DIR']);
        $car = $injector->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    public function testsGetInstance()
    {
        $compiler = new DiCompiler(new FakeCarModule, $_ENV['TMP_DIR']);
        $car = $compiler->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    public function testAopCompile()
    {
        $compiler = new DiCompiler(new FakeAopModule, $_ENV['TMP_DIR']);
        $compiler->compile();
        $files = [
            'Ray_Di_FakeAopInterface-*.php',
            'Ray_Di_FakeDoubleInterceptor-*.php'
        ];
        foreach ($files as $file) {
            $this->assertFileExists($_ENV['TMP_DIR'] . '/' . $file);
        }

        $this->testAopCompileFile();
    }

    /**
     * @depends testAopCompile
     */
    public function testAopCompileFile()
    {
        $script = new ScriptInjector($_ENV['TMP_DIR']);
        /** @var $instance FakeAop */
        $instance = $script->getInstance(FakeAopInterface::class);
        $this->assertInstanceOf(FakeAop::class, $instance);
        $this->assertInstanceOf(WeavedInterface::class, $instance);
        $result = $instance->returnSame(1);
        $expected = 2;
        $this->assertSame($expected, $result);
    }

    public function testInjectionPoint()
    {
        $compiler = new DiCompiler(new FakeLoggerModule, $_ENV['TMP_DIR']);
        $compiler->compile();
        $injector = new ScriptInjector($_ENV['TMP_DIR']);
        $loggerConsumer = $injector->getInstance(FakeLoggerConsumer::class);
        $this->assertSame('Ray\Di\FakeLoggerConsumer', $loggerConsumer->logger->name);
    }
}
