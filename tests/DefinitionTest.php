<?php

namespace Ray\Di;

require __DIR__ . '/../src.php';

/**
 * Test class for Annotation.
 */
class DefinitionTest extends \PHPUnit_Framework_TestCase
{
    protected $definition;

    protected function setUp()
    {
        parent::setUp();
        $this->definition = new Definition;
    }

    public function testOffsetSetWithKey()
    {
        $method = 'onEnd';
        $this->definition['MockDefiniton'] = array(Definition::PRE_DESTROY => $method);
        $actual = $this->definition['MockDefiniton'][Definition::PRE_DESTROY];
        $this->assertSame($method, $actual);
    }

    /**
     *
     * @expectedException InvalidArgumentException
     */
    public function testOffsetSetWithoutKey()
    {
        $method = 'onEnd';
        $this->definition[] = array('MockDefiniton' => array(Definition::PRE_DESTROY => $method));
        $actual = $this->definition['MockDefiniton'][Definition::PRE_DESTROY];
        v($this->definition);
        v($actual);
        $this->assertSame($method, $actual);
    }

    public function testOffsetExists()
    {
        $method = 'onEnd';
        $this->definition['MockDefiniton'] = array(Definition::PRE_DESTROY => $method);
        $condition = isset($this->definition['MockDefiniton'][Definition::PRE_DESTROY]);
        $this->assertTrue($condition);
    }

    public function testOffsetUnset()
    {
        $method = 'onEnd';
        $this->definition['MockDefiniton'] = array(Definition::PRE_DESTROY => $method);
        unset($this->definition['MockDefiniton']);
        $condition = isset($this->definition['MockDefiniton']);
        $this->assertFalse($condition);
    }

    public function testOffsetGet_ScopeDefaultIsPrototype()
    {
        $prototype = $this->definition[Definition::SCOPE];
        $expected = Scope::PROTOTYPE;
        $this->assertSame($expected, $prototype);
    }

    public function testIsString()
    {
        $str = (string)$this->definition;
        $this->assertSame(true, is_string($str));
    }

    public function testGetIterator()
    {
        $iterator = get_class($this->definition->getIterator());
        $this->assertSame('ArrayIterator', $iterator);
    }
}