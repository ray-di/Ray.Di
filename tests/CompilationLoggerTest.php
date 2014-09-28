<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;

class CompilationLoggerTest extends LoggerTest
{
    /**
     * @var CompilationLogger
     */
    protected $compilationLogger;

    protected function setUp()
    {
        $this->compilationLogger = (new CompilationLogger(new Logger))->setConfig(new Config(new Annotation(new Definition, new AnnotationReader)));
    }

    public function testNew()
    {
        $this->assertInstanceOf('Ray\Di\CompilationLogger', $this->compilationLogger);
    }

    /**
     * @expectedException \Ray\Di\Exception\Compile
     */
    public function testCompileException()
    {
        $invalidId = -1;
        $this->compilationLogger->newInstance($invalidId);
    }
}
