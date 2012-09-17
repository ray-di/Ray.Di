<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Test class for Config.
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $config;

    protected function setUp()
    {
        parent::setUp();
        $this->config = new Config(new Annotation(new Definition, new AnnotationReader));;
    }

    public function testFetchReadsConstructorDefaults()
    {
        $expect = ['foo' => 'bar'];
        list($actual_params, $actual_setter) = $this->config->fetch('Ray\Di\MockParentClass');
        $this->assertSame($expect, $actual_params);
    }

    /**
     * coverage for the "merged already" portion of the fetch() method
     */
    public function testFetchTwiceForMerge()
    {
        $expect = $this->config->fetch('Ray\Di\MockParentClass');
        $actual = $this->config->fetch('Ray\Di\MockParentClass');
        $this->assertSame($expect, $actual);
    }

    public function testFetchCapturesParentParams()
    {
        $expect = ['foo' => 'bar', 'zim' => null];

        list($actual_params, $actual_setter) = $this->config->fetch('Ray\Di\MockChildClass');
        $this->assertSame($expect, $actual_params);
    }

    public function testFetchCapturesExplicitParams()
    {
        $this->config = new Config(new Annotation(new Definition, new AnnotationReader));
        $params = $this->config->getParams();
        $params['Ray\Di\MockParentClass'] = ['foo' => 'zim'];

        $expect = ['foo' => 'zim'];
        list($actual_params, $actual_setter) = $this->config->fetch('Ray\Di\MockParentClass');
        $this->assertSame($expect, $actual_params);
    }

    public function testFetchHonorsExplicitParentParams()
    {
        $this->config = new Config(new Annotation(new Definition, new AnnotationReader));;
        $params = $this->config->getParams();
        $params['Ray\Di\MockParentClass'] = ['foo' => 'dib'];

        $expect = ['foo' => 'dib', 'zim' => null];

        list($actual_params, $actual_setter) = $this->config->fetch('Ray\Di\MockChildClass');
        $this->assertSame($expect, $actual_params);

        // for test coverage of the mock class
        $child = new \Ray\Di\MockChildClass('bar', new \Ray\Di\MockOtherClass());
    }

    public function testGetReflection()
    {
        $actual = $this->config->getReflect('Ray\Di\MockOtherClass');
        $this->assertInstanceOf('ReflectionClass', $actual);
        $this->assertSame('Ray\Di\MockOtherClass', $actual->getName());
        $actual = $this->config->getReflect('Ray\Di\MockOtherClass');
    }

    public function testFetchCapturesParentSetter()
    {
        $setter = $this->config->getSetter();
        $setter['Ray\Di\MockParentClass']['setFake'] = 'fake1';

        list($actual_config, $actual_setter) = $this->config->fetch('Ray\Di\MockChildClass');
        $expect = ['setFake' => 'fake1'];
        $this->assertSame($expect, $actual_setter);

    }

    public function testFetchCapturesOverrideSetter()
    {
        $setter = $this->config->getSetter();
        $setter['Ray\Di\MockParentClass']['setFake'] = 'fake1';
        $setter['Ray\Di\MockChildClass']['setFake'] = 'fake2';

        list($actual_config, $actual_setter) = $this->config->fetch('Ray\Di\MockChildClass');
        $expect = ['setFake' => 'fake2'];
        $this->assertSame($expect, $actual_setter);
    }

    public function testClone()
    {
        $this->config = new Config(new Annotation(new Definition, new AnnotationReader));;
        $clone = clone $this->config;

        $this->assertNotSame($clone, $this->config);
        $this->assertNotSame($clone->getParams(), $this->config->getParams());
        $this->assertNotSame($clone->getSetter(), $this->config->getSetter());
    }

    public function testFetchDefinition()
    {
        list($actual_config, $actual_setter, $definition) = $this->config->fetch('Ray\Di\Definition\MockDefinitionClass');
        $expect = 'onInit';
        $this->assertSame($expect, $definition['PostConstruct']);
    }

    public function testFetchParentDefinition()
    {
        list($actual_config, $actual_setter, $definition) = $this->config->fetch('Ray\Di\Definition\MockDefinitionChildClass');
        $expect = 'onInit';
        $this->assertSame($expect, $definition['PostConstruct']);
        // same
        $expect = 'prototype';
        $this->assertSame($expect, $definition['Scope']);
    }

    public function testFetchOverrideDefinition()
    {
        list($actual_config, $actual_setter, $definition) = $this->config->fetch('Ray\Di\Definition\MockDefinitionChildOverrideClass');
        $expect = 'onInit';
        $this->assertSame($expect, $definition['PostConstruct']);
        // changed
        $expect = 'singleton';
        $this->assertSame($expect, $definition['Scope']);
    }

    public function testConfigRetainDefintionAfterFetch()
    {
        $this->config->fetch('Ray\Di\Definition\MockDefinitionClass');
        $def = $this->config->getDefinition();
        $this->assertTrue($def['Ray\Di\Definition\MockDefinitionClass'] instanceof Definition);
    }

//     public function testConfigRetainDefintionAfterFetchChildClass()
//     {
//         $this->config->fetch('Ray\Di\Definition\MockDefinitionClass');
//         $this->config->fetch('Ray\Di\Definition\MockDefinitionChildClass');
//         $this->config->fetch('Ray\Di\Definition\MockDefinitionChildOverrideClass');
//         $def = $this->config->getDefinition();
//         $this->assertTrue($def['Ray\Di\Definition\MockDefinitionClass'] instanceof Definition);
//         $this->assertTrue($def['Ray\Di\Definition\MockDefinitionChildClass'] instanceof Definition);
//         $this->assertTrue($def['Ray\Di\Definition\MockDefinitionChildOverrideClass'] instanceof Definition);
//     }

    public function testGetMethodReflect()
    {
        $methodReflcet = $this->config->getMethodReflect('Ray\Di\Definition\MockDefinitionClass', 'setDouble');
        $this->assertInstanceOf('\ReflectionMethod', $methodReflcet);
        $this->assertSame('setDouble', $methodReflcet->name);
        $this->assertSame('Ray\Di\Definition\MockDefinitionClass', $methodReflcet->class);
    }

    public function testGetMethodReflectObject()
    {
        $methodReflcet = $this->config->getMethodReflect(new \Ray\Di\Definition\MockDefinitionClass(new Forge(new Config(new Annotation(new Definition, new AnnotationReader))), 2), 'setDouble');
        $this->assertInstanceOf('\ReflectionMethod', $methodReflcet);
        $this->assertSame('setDouble', $methodReflcet->name);
        $this->assertSame('Ray\Di\Definition\MockDefinitionClass', $methodReflcet->class);
    }

    public function testEnableSerialize()
    {
        $expect = $this->config->fetch('Ray\Di\Definition\MockDefinitionClass');
        $serialize = serialize($this->config);
        $this->assertTrue(is_string($serialize));
    }

    public function getDefinitionCache()
    {
        $expect = $this->config->getDefinition('Ray\Di\MockParentClass');
        $actual = $this->config->getDefinition('Ray\Di\MockParentClass');
        $this->assertSame($expect, $actual);
    }
}
