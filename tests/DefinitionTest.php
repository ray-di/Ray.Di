<?php

namespace Ray\Di;

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
        $this->definition['MockDefiniton'] = [Definition::PRE_DESTROY => $method];
        $actual = $this->definition['MockDefiniton'][Definition::PRE_DESTROY];
        $this->assertSame($method, $actual);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testOffsetSetWithoutKey()
    {
        $method = 'onEnd';
        $this->definition[] = ['MockDefiniton' => [Definition::PRE_DESTROY => $method]];
        $actual = $this->definition['MockDefiniton'][Definition::PRE_DESTROY];
    }

    public function testOffsetExists()
    {
        $method = 'onEnd';
        $this->definition['MockDefiniton'] = [Definition::PRE_DESTROY => $method];
        $condition = isset($this->definition['MockDefiniton'][Definition::PRE_DESTROY]);
        $this->assertTrue($condition);
    }

    public function testOffsetUnset()
    {
        $method = 'onEnd';
        $this->definition['MockDefiniton'] = [Definition::PRE_DESTROY => $method];
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
        $str = (string) $this->definition;
        $this->assertSame(true, is_string($str));
    }

    public function testGetIterator()
    {
        $iterator = get_class($this->definition->getIterator());
        $this->assertSame('ArrayIterator', $iterator);
    }

    public function test_setUserAnnotationMethodName()
    {
        $annotationName = 'anno1';
        $methodName = 'method1';
        $methodAnnotation = 'method_annotation1';
        $this->definition->setUserAnnotationMethodName($annotationName, $methodName);
        $result = $this->definition->getUserAnnotationMethodName($annotationName);
        $this->assertSame([$methodName], $result);
    }

    public function test_setUserAnnotationMethodNameMulti()
    {
        $annotationName = 'anno1';
        $methodName = 'method1';
        $methodName2 = 'method2';
        $methodAnnotation = 'method_annotation1';
        $this->definition->setUserAnnotationMethodName($annotationName, $methodName);
        $this->definition->setUserAnnotationMethodName($annotationName, $methodName2);
        $result = $this->definition->getUserAnnotationMethodName($annotationName);
        $this->assertSame([$methodName, $methodName2], $result);
    }

    public function test_setUserAnnotationByMethod()
    {
        $annotationName = 'anno1';
        $methodName = 'method1';
        $methodAnnotation = 'method_annotation1';
        $this->definition->setUserAnnotationByMethod($annotationName, $methodName, $methodAnnotation);
        $result = $this->definition->getUserAnnotationByMethod($methodName);
        $expected = ['anno1' => ['method_annotation1']];
        $this->assertSame($expected,  $result);
    }

    public function test_setUserAnnotationByMulti2()
    {
        $annotationName = 'anno1';
        $annotationName2 = 'anno2';
        $methodName = 'method1';
        $methodAnnotation = 'method_annotation1';
        $methodAnnotation2 = 'method_annotation2';
        $this->definition->setUserAnnotationByMethod($annotationName, $methodName, $methodAnnotation);
        $this->definition->setUserAnnotationByMethod($annotationName2, $methodName, $methodAnnotation2);
        $result = $this->definition->getUserAnnotationByMethod($methodName);
        $expected = [$annotationName => [$methodAnnotation], $annotationName2 => [$methodAnnotation2]];
        $this->assertSame($expected,  $result);
    }
}
