<?php
namespace Ray\Di;

use Ray\Aop\Bind;

class TestObject
{
    public function __construct($c1, $c2)
    {
    }

    public function setA($a)
    {
    }

    public function setB($b)
    {
    }

    public function setCallable(callable $c)
    {
    }
}

function someFunction()
{
    return 1;
}

class DiLoggerTest extends \PHPUnit_Framework_TestCase
{

    private $diLogger;

    protected function setUp()
    {
        $this->diLogger = new Logger;
    }

    protected function tearDown()
    {
    }

    public function testNew()
    {
        $this->assertInstanceOf('Ray\Di\Logger', $this->diLogger);
    }

    public function testLog()
    {
        $params = ["a", 1];
        $setter = ['setA' => null, 'setB' => null];
        $object = (new \ReflectionClass(__NAMESPACE__ . '\TestObject'))->newInstanceArgs($params);
        $this->diLogger->log('Class', $params, $setter, $object, new Bind);
        $expected = '[DI] Class construct[(string) a, (integer) 1] setter[setA, setB]';
        $this->assertSame($expected, (string)$this->diLogger);
    }

    public function testLogCallableParam()
    {
        $params = [1.0, __NAMESPACE__ . '\someFunction'];
        $setter = ['setA' => null, 'setB' => null];
        $object = (new \ReflectionClass(__NAMESPACE__ . '\TestObject'))->newInstanceArgs($params);
        $this->diLogger->log('Class', $params, $setter, $object, new Bind);
        $expected = '[DI] Class construct[(double) 1, (callable) Ray\Di\someFunction] setter[setA, setB]';
        $this->assertSame($expected, (string)$this->diLogger);
    }

    public function testLogArrayParam()
    {
        $params = [1, ['a1', 'a2']];
        $setter = ['setA' => null, 'setB' => null];
        $object = (new \ReflectionClass(__NAMESPACE__ . '\TestObject'))->newInstanceArgs($params);
        $this->diLogger->log('Class', $params, $setter, $object, new Bind);
        $expected = '[DI] Class construct[(integer) 1, Array([0]=>a1[1]=>a2)] setter[setA, setB]';
        $this->assertSame($expected, (string)$this->diLogger);
    }

    public function testLogObjectParam()
    {
        $stdObj = new \stdClass;
        $params = [1, $stdObj];
        $setter = ['setA' => null, 'setB' => null];
        $object = (new \ReflectionClass(__NAMESPACE__ . '\TestObject'))->newInstanceArgs($params);
        $diLogger = $this->diLogger;
        $this->diLogger->log('Class', $params, $setter, $object, new Bind);
        $diLogger->log('Class', $params, $setter, $object, new Bind);
        $this->assertSame((string)$diLogger, (string)$this->diLogger);
    }
}
