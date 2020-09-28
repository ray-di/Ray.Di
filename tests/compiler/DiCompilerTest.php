<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PHPUnit\Framework\TestCase;
use Ray\Aop\WeavedInterface;
use Ray\Compiler\Exception\Unbound;
use Ray\Di\Name;

class DiCompilerTest extends TestCase
{
    public function testUnbound() : void
    {
        $this->expectException(Unbound::class);
        $injector = new ScriptInjector($_ENV['TMP_DIR']);
        $injector->getInstance(FakeCarInterface::class);
    }

    public function testCompile() : void
    {
        $compiler = new DiCompiler(new FakeCarModule, $_ENV['TMP_DIR']);
        $compiler->compile();
        $any = Name::ANY;
        $files = [
            "Ray_Compiler_FakeCarInterface-{$any}.php",
            "Ray_Compiler_FakeEngineInterface-{$any}.php",
            "Ray_Compiler_FakeHandleInterface-{$any}.php",
            "Ray_Compiler_FakeHardtopInterface-{$any}.php",
            'Ray_Compiler_FakeMirrorInterface-right.php',
            'Ray_Compiler_FakeMirrorInterface-left.php',
            "Ray_Compiler_FakeTyreInterface-{$any}.php",
        ];
        foreach ($files as $file) {
            $filePath = $_ENV['TMP_DIR'] . '/' . $file;
            $this->assertFileExists($filePath, $filePath);
        }
        $injector = new ScriptInjector($_ENV['TMP_DIR']);
        $car = $injector->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    public function testsGetInstance() : void
    {
        $compiler = new DiCompiler(new FakeCarModule, $_ENV['TMP_DIR']);
        $car = $compiler->getInstance(FakeCarInterface::class);
        $this->assertInstanceOf(FakeCar::class, $car);
    }

    public function testAopCompile() : void
    {
        $compiler = new DiCompiler(new FakeAopModule, $_ENV['TMP_DIR']);
        $compiler->compile();
        $any = Name::ANY;
        $files = [
            "Ray_Compiler_FakeAopInterface-{$any}.php",
            "Ray_Compiler_FakeDoubleInterceptor-{$any}.php"
        ];
        foreach ($files as $file) {
            $this->assertFileExists($_ENV['TMP_DIR'] . '/' . $file);
        }

        $this->testAopCompileFile();
    }

    /**
     * @depends testAopCompile
     */
    public function testAopCompileFile() : void
    {
        $script = new ScriptInjector($_ENV['TMP_DIR']);
        /** @var FakeAop $instance */
        $instance = $script->getInstance(FakeAopInterface::class);
        $this->assertInstanceOf(FakeAop::class, $instance);
        $this->assertInstanceOf(WeavedInterface::class, $instance);
        $result = $instance->returnSame(1);
        $expected = 2;
        $this->assertSame($expected, $result);
    }

    public function testInjectionPoint() : void
    {
        $compiler = new DiCompiler(new FakeLoggerModule, $_ENV['TMP_DIR']);
        $compiler->compile();
        $injector = new ScriptInjector($_ENV['TMP_DIR']);
        $loggerConsumer = $injector->getInstance(FakeLoggerConsumer::class);
        /* @var $loggerConsumer \Ray\Compiler\FakeLoggerConsumer */
        $this->assertSame('Ray\Compiler\FakeLoggerConsumer', $loggerConsumer->logger->name);
        $this->assertSame('MEMORY', $loggerConsumer->logger->type);
    }

    public function testDump() : void
    {
        $compiler = new DiCompiler(new FakeCarModule, $_ENV['TMP_DIR']);
        $compiler->dumpGraph();
        $any = Name::ANY;
        $this->assertFileExists($_ENV['TMP_DIR'] . '/graph/Ray_Compiler_FakeCarInterface-' . $any . '.html');
    }

    /**
     * @return array<int, array<int, null|array<int|string, int>|float|int|string|true>>
     */
    public function instanceProvider() : array
    {
        return [
            ['bool', true],
            ['null', null],
            ['int', 1],
            ['float', 1.0],
            ['string', 'ray'],
            ['no_index_array', [1, 2]],
            ['assoc', ['a' => 1]]
        ];
    }

    /**
     * @dataProvider instanceProvider
     *
     * @param null|array<int|string, int>|float|int|string|true $expected
     */
    public function testInstance(string $name, $expected) : void
    {
        $compiler = new DiCompiler(new FakeInstanceModule, $_ENV['TMP_DIR']);
        $compiler->compile();
        $injector = new ScriptInjector($_ENV['TMP_DIR']);
        $result = $injector->getInstance('', $name);
        $this->assertSame($expected, $result);
        $object = $injector->getInstance('', 'object');
        $this->assertInstanceOf(\DateTime::class, $object);
    }
}
