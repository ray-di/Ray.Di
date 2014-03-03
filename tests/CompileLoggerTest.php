<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;

class CompileLoggerTest extends LoggerTest
{
    protected function setUp()
    {
        $this->diLogger = (new CompileLogger(new Logger))->setConfig(new Config(new Annotation(new Definition, new AnnotationReader)));
    }

    public function testNew()
    {
        $this->assertInstanceOf('Ray\Di\CompileLogger', $this->diLogger);
    }
}
