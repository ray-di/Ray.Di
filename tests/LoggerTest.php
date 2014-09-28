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
    protected $compilationLogger;

    protected function setUp()
    {
        $this->compilationLogger = new Logger;
    }

    protected function tearDown()
    {
    }

    public function testNew()
    {
        $this->assertInstanceOf('Ray\Di\Logger', $this->compilationLogger);
    }

    public function testLog()
    {
        $params = ["a", 1];
        $setter = ['setA' => [null], 'setB' => [null]];
        $object = (new \ReflectionClass(__NAMESPACE__ . '\TestObject'))->newInstanceArgs($params);
        $definition = new BoundDefinition;
        $definition->class = 'Ray\Di\Mock\Db';
        $this->compilationLogger->log($definition, $params, $setter, $object, new Bind);
        $this->assertInternalType('string', (string) $this->compilationLogger);
    }

    public function testLogCallableParam()
    {
        $params = [1.0, __NAMESPACE__ . '\someFunction'];
        $setter = ['setA' => [null], 'setB' => [null], 'setCallable' => [__NAMESPACE__ . '\someFunction'] ];
        $object = (new \ReflectionClass(__NAMESPACE__ . '\TestObject'))->newInstanceArgs($params);
        $definition = new BoundDefinition;
        $definition->class = 'Ray\Di\Mock\Db';
        $this->compilationLogger->log($definition, $params, $setter, $object, new Bind);
        $this->assertInternalType('string', (string) $this->compilationLogger);
    }

    public function testLogArrayParam()
    {
        $params = [1, ['a1', 'a2']];
        $setter = ['setA' => [null], 'setB' => [null]];
        $object = (new \ReflectionClass(__NAMESPACE__ . '\TestObject'))->newInstanceArgs($params);
        $definition = new BoundDefinition;
        $definition->class = 'Ray\Di\Mock\Db';
        $this->compilationLogger->log($definition, $params, $setter, $object, new Bind);
        $this->assertInternalType('string', (string) $this->compilationLogger);
    }

    public function testSerialize()
    {
        $unserializableObject = new TestObject(function () {}, new \PDO('sqlite::memory:'));
        $definition = new BoundDefinition;
        $definition->class = 'Ray\Di\Mock\Db';
        $this->compilationLogger->log($definition, [], [], $unserializableObject, new Bind);
        $serialized = serialize($this->compilationLogger);
        $this->assertInternalType('string', $serialized);
    }

    public function testUnserialize()
    {
        $unserializableObject = new TestObject(function () {}, new \PDO('sqlite::memory:'));
        $definition = new BoundDefinition;
        $definition->class = 'Ray\Di\Mock\Db';
        $this->compilationLogger->log($definition, [], [], $unserializableObject, new Bind);
        $unSerialized = unserialize(serialize($this->compilationLogger));
        /** @var Logger $unSerialized */

        $this->assertInstanceOf(get_class($this->compilationLogger), $unSerialized);
    }
}
