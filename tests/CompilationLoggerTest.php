<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;

class CompilationLoggerTest extends LoggerTest
{
    protected function setUp()
    {
        $this->diLogger = (new CompilationLogger(new Logger))->setConfig(new Config(new Annotation(new Definition, new AnnotationReader)));
    }

    public function testNew()
    {
        $this->assertInstanceOf('Ray\Di\CompilationLogger', $this->diLogger);
    }
}
