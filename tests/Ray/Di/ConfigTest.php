<?php
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Scope;

/**
 * Test class for Config.
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $config;

    protected function setUp()
    {
        parent::setUp();
        $this->config = new Config(new Annotation(new Definition, new AnnotationReader));;
    }

    public function testFetchReadsConstructorDefaults()
    {
        $expect = ['foo' => 'bar'];
        list($actual_params, ) = $this->config->fetch('Ray\Di\MockParentClass');
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

        list($actual_params, ) = $this->config->fetch('Ray\Di\MockChildClass');
        $this->assertSame($expect, $actual_params);
    }

    public function testFetchCapturesExplicitParams()
    {
        $this->config = new Config(new Annotation(new Definition, new AnnotationReader));
        $params = $this->config->getParams();
        $params['Ray\Di\MockParentClass'] = ['foo' => 'zim'];

        $expect = ['foo' => 'zim'];
        list($actual_params, ) = $this->config->fetch('Ray\Di\MockParentClass');
        $this->assertSame($expect, $actual_params);
    }

    public function testFetchHonorsExplicitParentParams()
    {
        $this->config = new Config(new Annotation(new Definition, new AnnotationReader));;
        $params = $this->config->getParams();
        $params['Ray\Di\MockParentClass'] = ['foo' => 'dib'];

        $expect = ['foo' => 'dib', 'zim' => null];

        list($actual_params, ) = $this->config->fetch('Ray\Di\MockChildClass');
        $this->assertSame($expect, $actual_params);

        // for test coverage of the mock class
        new MockChildClass('bar', new MockOtherClass());
    }

    public function testGetReflection()
    {
        $actual = $this->config->getReflect('Ray\Di\MockOtherClass');
        $this->assertInstanceOf('ReflectionClass', $actual);
        /** @var \ReflectionClass $actual */
        $this->assertSame('Ray\Di\MockOtherClass', $actual->getName());
        $this->config->getReflect('Ray\Di\MockOtherClass');
    }

    public function testFetchCapturesParentSetter()
    {
        $setter = $this->config->getSetter();
        $setter['Ray\Di\MockParentClass']['setFake'] = 'fake1';

        list(, $actual_setter) = $this->config->fetch('Ray\Di\MockChildClass');
        $expect = ['setFake' => 'fake1'];
        $this->assertSame($expect, $actual_setter);

    }

    public function testFetchCapturesOverrideSetter()
    {
        $setter = $this->config->getSetter();
        $setter['Ray\Di\MockParentClass']['setFake'] = 'fake1';
        $setter['Ray\Di\MockChildClass']['setFake'] = 'fake2';

        list(, $actual_setter) = $this->config->fetch('Ray\Di\MockChildClass');
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
        list(, , $definition) = $this->config->fetch('Ray\Di\Definition\MockDefinitionClass');
        $expect = 'onInit';
        $this->assertSame($expect, $definition['PostConstruct']);
    }

    public function testFetchParentDefinition()
    {
        list(, , $definition) = $this->config->fetch('Ray\Di\Definition\MockDefinitionChildClass');
        $expect = 'onInit';
        $this->assertSame($expect, $definition['PostConstruct']);
        // same
        $expect = Scope::PROTOTYPE;
        $this->assertSame($expect, $definition['Scope']);
    }

    public function testFetchOverrideDefinition()
    {
        list(, , $definition) = $this->config->fetch('Ray\Di\Definition\MockDefinitionChildOverrideClass');
        $expect = 'onInit';
        $this->assertSame($expect, $definition['PostConstruct']);
        // changed
        $expect = Scope::SINGLETON;
        $this->assertSame($expect, $definition['Scope']);
    }

    public function testConfigRetainDefinitionAfterFetch()
    {
        $this->config->fetch('Ray\Di\Definition\MockDefinitionClass');
        $def = $this->config->getDefinition();
        $this->assertTrue($def['Ray\Di\Definition\MockDefinitionClass'] instanceof Definition);
    }

     public function testConfigRetainDefinitionAfterFetchChildClass()
     {
         $this->config->fetch('Ray\Di\Definition\MockDefinitionClass');
         $this->config->fetch('Ray\Di\Definition\MockDefinitionChildClass');
         $this->config->fetch('Ray\Di\Definition\MockDefinitionChildOverrideClass');
         $def = $this->config->getDefinition();
         $this->assertTrue($def['Ray\Di\Definition\MockDefinitionClass'] instanceof Definition);
         $this->assertTrue($def['Ray\Di\Definition\MockDefinitionChildClass'] instanceof Definition);
         $this->assertTrue($def['Ray\Di\Definition\MockDefinitionChildOverrideClass'] instanceof Definition);
     }

    public function testGetMethodReflect()
    {
        $methodReflcet = $this->config->getMethodReflect('Ray\Di\Definition\MockDefinitionClass', 'setDouble');
        $this->assertInstanceOf('\ReflectionMethod', $methodReflcet);
        $this->assertSame('setDouble', $methodReflcet->name);
        $this->assertSame('Ray\Di\Definition\MockDefinitionClass', $methodReflcet->class);
    }

    public function testGetMethodReflectObject()
    {
        $methodReflcet = $this->config->getMethodReflect(new Definition\MockDefinitionClass(new Forge(new Config(new Annotation(new Definition, new AnnotationReader))), 2), 'setDouble');
        $this->assertInstanceOf('\ReflectionMethod', $methodReflcet);
        $this->assertSame('setDouble', $methodReflcet->name);
        $this->assertSame('Ray\Di\Definition\MockDefinitionClass', $methodReflcet->class);
    }

    public function testEnableSerialize()
    {
        $serialize = serialize($this->config);
        $this->assertTrue(is_string($serialize));
    }

    public function testGetDefinitionCache()
    {
        $expect = $this->config->getDefinition('Ray\Di\MockParentClass');
        $actual = $this->config->getDefinition('Ray\Di\MockParentClass');
        $this->assertSame($expect, $actual);
    }
}
