<?php
namespace Ray\Di;

class CompileLoggerTest extends LoggerTest
{
    protected function setUp()
    {
        $this->diLogger = new CompileLogger(new Logger);
    }

    public function testNew()
    {
        $this->assertInstanceOf('Ray\Di\CompileLogger', $this->diLogger);
    }
}
