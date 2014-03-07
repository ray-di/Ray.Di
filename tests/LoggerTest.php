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

class LoggerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var LoggerInterface
     */
    protected $diLogger;

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
        $setter = ['setA' => [null], 'setB' => [null]];
        $object = (new \ReflectionClass(__NAMESPACE__ . '\TestObject'))->newInstanceArgs($params);
        $this->diLogger->log('Ray\Di\Mock\Db', $params, $setter, $object, new Bind);
        $this->assertInternalType('string', (string)$this->diLogger);
    }

    public function testLogCallableParam()
    {
        $params = [1.0, __NAMESPACE__ . '\someFunction'];
        $setter = ['setA' => [null], 'setB' => [null], 'setCallable' => [__NAMESPACE__ . '\someFunction'] ];
        $object = (new \ReflectionClass(__NAMESPACE__ . '\TestObject'))->newInstanceArgs($params);
        $this->diLogger->log('Ray\Di\Mock\Db', $params, $setter, $object, new Bind);
        $this->assertInternalType('string', (string)$this->diLogger);
    }

    public function testLogArrayParam()
    {
        $params = [1, ['a1', 'a2']];
        $setter = ['setA' => [null], 'setB' => [null]];
        $object = (new \ReflectionClass(__NAMESPACE__ . '\TestObject'))->newInstanceArgs($params);
        $this->diLogger->log('Ray\Di\Mock\Db', $params, $setter, $object, new Bind);
        $this->assertInternalType('string', (string)$this->diLogger);
    }

    public function testLogObjectParam()
    {
        $stdObj = new \stdClass;
        $params = [1, $stdObj];
        $setter = ['setA' => [null], 'setB' => [null]];
        $object = (new \ReflectionClass(__NAMESPACE__ . '\TestObject'))->newInstanceArgs($params);
        $diLogger = $this->diLogger;
        $this->diLogger->log('Ray\Di\Mock\Db', $params, $setter, $object, new Bind);
        $diLogger->log('Ray\Di\Mock\Db', $params, $setter, $object, new Bind);
        $this->assertSame((string)$diLogger, (string)$this->diLogger);
    }

    public function testSerialize()
    {
        $unserializableObject = new TestObject(function(){}, new \PDO('sqlite::memory:'));
        $this->diLogger->log('Ray\Di\Mock\Db', [], [], $unserializableObject, new Bind);
        $serialized = serialize($this->diLogger);
        $this->assertInternalType('string', $serialized);
    }

    public function testUnserialize()
    {
        $unserializableObject = new TestObject(function(){}, new \PDO('sqlite::memory:'));
        $this->diLogger->log('Ray\Di\Mock\Db', [], [], $unserializableObject, new Bind);
        $unSerialized = unserialize(serialize($this->diLogger));
        /** @var Logger $unSerialized */

        $this->assertInstanceOf(get_class($this->diLogger), $unSerialized);
    }
}
